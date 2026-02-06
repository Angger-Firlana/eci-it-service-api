<?php

namespace App\Services\ServiceRequest;

use App\Enums\ServiceRequestStatusCode;
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
        $serviceRequest->timeline = $this->auditLogService->getTimeLineForServiceRequest($auditLogs, $serviceRequest);

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

    //
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
            'status_id'        => $this->getServiceRequestStatusId(ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);
    }

    //function to allowed transitions
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

    //function to get stats
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

    //function to update service request
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
                'status_id' => $newStatusId,
            ]));

            if (isset($data['details'])) {
                $this->syncDetails($serviceRequest, $data['details']);
            }

            if ($status->code === ServiceRequestStatusCode::IN_PROGRESS->value && $oldStatusId != $newStatusId) {
                $this->invoiceService->createInvoiceForServiceRequest($serviceRequest, $data);
            }

            return $this->loadRelations($serviceRequest);
        });
    }

    //function to delete service request
    public function deleteServiceRequest(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->loadMissing('status');
        if ($serviceRequest->status?->code === ServiceRequestStatusCode::COMPLETED->value) {
            throw new \Exception('Cannot delete completed service request');
        }

        $serviceRequest->delete();

        return $serviceRequest;
    }

    //function to generate service number
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

    //function to load relations 
    private function loadRelations(ServiceRequest $serviceRequest): ServiceRequest
    {
        return $serviceRequest->load($this->defaultWith());
    }

    //function to get relations for index
    private function indexWith(): array
    {
        return [
            'user',
            'admin',
            'status'
        ];
    }

    //function to get relations for show
    private function showWith(): array
    {
        return [
            'user:id,name,email',
            'user.departments:id,name',
            'admin:id,name,email',
            'admin.departments:id,name',
            'status:id,name,code',
            'service_request_details:id,service_request_id,service_type_id,device_id,complaint',
            'service_request_details.device:id,device_model_id,serial_number',
            'service_request_details.device.device_model:id,device_type_id,brand,model',
            'service_request_details.device.device_model.device_type:id,name',
            'service_request_details.service_type:id,name',
            'service_request_details.complaint_images',
            'vendor_approvals:id,service_request_id,approver_id,assigned_by,assigned_at,approved_at,status_id,created_at,updated_at',
            'vendor_approvals.status:id,name,code',
            'vendor_approvals.approver:id,name',
            'vendor_approvals.assigned_by:id,name'
        ];


    }

    //function to get default relations
    private function defaultWith(): array
    {
        return [
            'user',
            'status',
            'service_request_details.device',
        ];
    }

    //function to sync details
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

    //function to get service request status
    private function getServiceRequestStatusOrFail(int $statusId): Status
    {
        $status = Status::with('entity_type')->findOrFail($statusId);

        if ($status->entity_type?->code !== 'SERVICE_REQUEST') {
            throw new \Exception('Status tidak valid');
        }

        return $status;
    }

    //function to get service request status id
    private function getServiceRequestStatusId(ServiceRequestStatusCode|string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}
