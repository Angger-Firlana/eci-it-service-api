<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceRequest\DetailServiceRequestService;
use App\Models\StatusTransition;
use App\Services\AuditLogService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Role;

class ServiceRequestService
{
    protected DetailServiceRequestService $detailService;
    protected \App\Services\InvoiceService $invoiceService;
    protected AuditLogService $auditLogService;

    public function __construct(
        DetailServiceRequestService $detailService,
        \App\Services\InvoiceService $invoiceService,
        AuditLogService $auditLogService
    ) {
        $this->detailService = $detailService;
        $this->invoiceService = $invoiceService;
        $this->auditLogService = $auditLogService;
    }

    public function getAllServiceRequest(Request $request): LengthAwarePaginator
    {
        return ServiceRequest::with($this->indexWith())
            ->filter($request)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
    }   

    public function getServiceRequestById(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::with($this->showWith())->findOrFail($id);

        $serviceRequest->makeHidden([
            'created_at',
            'updated_at'
        ]);

        $auditLogs = $this->auditLogService->getAuditLogsForServiceRequest($serviceRequest);
        $serviceRequest->audit_logs = $auditLogs;
        $serviceRequest->service_request_approvals = $serviceRequest->vendor_approvals;
        $serviceRequest->timeline = $auditLogs->map(function ($log) use ($serviceRequest) {
            $label = $log->action === 'CREATE_REQUEST'
                ? ($serviceRequest->status->name ?? 'Menunggu Approval')
                : ($log->action === 'UPDATE_STATUS' ? 'Status diubah' : $log->action);

            $actorName = $log->actor->name ?? 'Unknown';
            $description = $log->action === 'CREATE_REQUEST'
                ? "Request dibuat oleh {$actorName}"
                : $log->notes;

            return [
                'id' => $log->id,
                'label' => $label,
                'status' => $label,
                'date' => $log->created_at,
                'created_at' => $log->created_at,
                'note' => $description,
                'description' => $description,
                'state' => 'active',
            ];
        });

        return $serviceRequest;
    }

    public function createServiceRequest(array $data): ServiceRequest
    {
        return DB::transaction(function () use ($data) {
            $serviceRequest = $this->createMainServiceRequest($data);
            $this->createServiceRequestDetails($serviceRequest, $data['details'] ?? []);

            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id() ?? $serviceRequest->user_id,
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'action' => 'CREATE_REQUEST',
                'notes' => 'Request dibuat',
                'old_status_id' => $serviceRequest->status_id,
                'new_status_id' => $serviceRequest->status_id,
            ]);

            return $this->loadRelations($serviceRequest);
        });
    }

    private function createServiceRequestDetails(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {
            $this->detailService->createDetailServiceRequest(array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }

    private function createMainServiceRequest(array $data): ServiceRequest
    {
        $adminId = null;
        $userId = null;
        $user = Auth::user();

        if ($user && $user->roles->contains('id', Role::ADMIN)) {
            $adminId = $data['admin_id'] ?? null;
        }

        if ($user && $user->roles->contains('id', Role::USER)) {
            $userId = $user->id;
        }

        return ServiceRequest::create([
            'service_number'   => $this->generateServiceNumber(),
            'admin_id'         => $adminId,
            'user_id'          => $userId,
            'request_date'     => now(),
            'estimated_date'   => $data['estimated_date'] ?? null,
            'status_id'        => $data['status_id'] ?? Status::PENDING,
        ]);
    }


    public function getAllowedTransitions(int $serviceRequestId): Collection
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $user = Auth::user();
        
        // If user is not authenticated, return empty
        if (!$user) {
            return collect([]);
        }

        $userRoles = $user->roles->pluck('id');

        $transitions = StatusTransition::where('from_status_id', $serviceRequest->status_id)
            ->whereHas('roles', function($q) use ($userRoles) {
                $q->whereIn('roles.id', $userRoles);
            })
            ->with('status')
            ->get();
            
        return $transitions->pluck('status');
    }

    public function getStats(): array
    {
        return [
            'total' => ServiceRequest::count(),
            'by_status' => ServiceRequest::select('status_id', DB::raw('count(*) as count'))
                ->with('status:id,name,code')
                ->groupBy('status_id')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status->name,
                        'code' => $item->status->code,
                        'count' => $item->count
                    ];
                }),
            'recent' => ServiceRequest::orderBy('created_at', 'desc')->take(5)->get()
        ];
    }

    public function updateServiceRequest(int $id, array $data): ServiceRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $serviceRequest = ServiceRequest::findOrFail($id);
            $oldStatusId = $serviceRequest->status_id;

            $newStatusId = $data['status_id'] ?? $serviceRequest->status_id;
            $status = $this->getServiceRequestStatusOrFail($newStatusId);

            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id() ?? $serviceRequest->user_id,
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'action' => 'UPDATE_STATUS',
                'notes' => $data['log_notes'] ?? "Status {$serviceRequest->status->name} to {$status->name}",
                'old_status_id' => $oldStatusId,
                'new_status_id' => $newStatusId,
            ]);

            if(auth()->user()->roles->contains('id', Role::ADMIN)){
                $serviceRequest->update([
                    'admin_id' => $data['admin_id'] ?? $serviceRequest->admin_id,
                ]);
            }

            $serviceRequest->update(array_filter([
                'estimated_date' => $data['estimated_date'] ?? $serviceRequest->estimated_date,
                'status_id' => $data['status_id'] ?? $serviceRequest->status_id,
            ]));

            if (isset($data['details'])) {
                $this->syncDetails($serviceRequest, $data['details']);
            }

            if ($newStatusId == 8 && $oldStatusId != 8) {
                $this->invoiceService->createInvoiceForServiceRequest($serviceRequest, $data);
            }

            return $this->loadRelations($serviceRequest);
        });
    }

    public function deleteServiceRequest(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        if ($serviceRequest->status_id == 3) {
            throw new \Exception('Cannot delete completed service request');
        }

        $serviceRequest->delete();

        return $serviceRequest;
    }

    private function generateServiceNumber(): string
    {
        $prefix = 'SR';
        $date = now()->format('Ymd');
        $lastService = ServiceRequest::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        $sequence = $lastService ? (int) substr($lastService->service_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function loadRelations(ServiceRequest $serviceRequest): ServiceRequest
    {
        return $serviceRequest->load($this->defaultWith());
    }

    private function indexWith(): array
    {
        return [
            'user',
            'admin',
            'status'
        ];
    }

    private function showWith(): array
    {
        return [
            'user:id,name,email',
            'user.departments:id,name',
            'admin:id,name,email',
            'status:id,name',
            'service_request_details:id,service_request_id,service_type_id,device_id,complaint',
            'service_request_details.device:id,device_model_id,serial_number',
            'service_request_details.device.device_model:id,device_type_id,brand,model',
            'service_request_details.device.device_model.device_type:id,name',
            'service_request_details.service_type:id,name',
            'service_request_details.complaint_images',
            'vendor_approvals:id,service_request_id,approver_id,assigned_by,assigned_at,approved_at,status_id,created_at,updated_at',
            'vendor_approvals.status:id,name',
            'vendor_approvals.approver:id,name',
            'vendor_approvals.assigned_by:id,name'
        ];


    }

    private function defaultWith(): array
    {
        return [
            'user',
            'status',
            'service_request_details.device',
        ];
    }

    private function syncDetails(ServiceRequest $serviceRequest, array $details): void
    {
        foreach ($details as $detail) {
            if (isset($detail['id'])) {
                $this->detailService->updateDetailServiceRequest($detail['id'], $detail);
                continue;
            }

            $this->detailService->createDetailServiceRequest(array_merge($detail, [
                'service_request_id' => $serviceRequest->id,
            ]));
        }
    }

    private function getServiceRequestStatusOrFail(int $statusId): Status
    {
        $status = Status::findOrFail($statusId);

        if ($status->entity_type_id != 1) {
            throw new \Exception('Status tidak valid');
        }

        return $status;
    }
}
