<?php

namespace App\Services\ServiceRequest;

use App\Enums\ServiceRequestStatusCode;

use App\Helpers\ServiceRequest\ShowRelationsHandler;
use App\Helpers\ServiceRequest\StatusHandler;

use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\StatusTransition;
use App\Models\Role;
use App\Models\Device;

use App\Services\ServiceRequest\DetailServiceRequestService;
use App\Services\ServiceRequest\ServiceRequestIdempotencyHandler;
use App\Services\AuditLog\AuditLogService;
use App\Services\Invoice\InvoiceService;
use App\Services\ContactAdmin\ContactAdminMailservice;
use App\Services\Notification\NotificationService;
use App\Services\ServiceRequest\ServiceRequestApprovalService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ServiceRequestService
{
    protected DetailServiceRequestService $detailService;
    protected ContactAdminMailservice $contactAdminMailService;
    protected InvoiceService $invoiceService;
    protected AuditLogService $auditLogService;
    protected ServiceRequestApprovalService $serviceRequestApprovalService;
    protected ServiceRequestIdempotencyHandler $serviceRequestIdempotencyHandler;
    protected ShowRelationsHandler $showRelationsHandler;
    protected NotificationService $notificationService;

    public function __construct(
        DetailServiceRequestService $detailService,
        ContactAdminMailservice $contactAdminMailService,
        InvoiceService $invoiceService,
        AuditLogService $auditLogService,
        ServiceRequestApprovalService $serviceRequestApprovalService,
        ServiceRequestIdempotencyHandler $serviceRequestIdempotencyHandler,
        ShowRelationsHandler $showRelationsHandler,
        NotificationService $notificationService
    ) {
        $this->detailService = $detailService;
        $this->contactAdminMailService = $contactAdminMailService;
        $this->invoiceService = $invoiceService;
        $this->auditLogService = $auditLogService;
        $this->serviceRequestApprovalService = $serviceRequestApprovalService;
        $this->serviceRequestIdempotencyHandler = $serviceRequestIdempotencyHandler;
        $this->showRelationsHandler = $showRelationsHandler;
        $this->notificationService = $notificationService;
    }

    public function getAllServiceRequest(Request $request): LengthAwarePaginator
    {
        return ServiceRequest::with($this->showRelationsHandler->indexWith())
            ->filter($request)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
    }   

    public function getServiceRequestById(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::with($this->showRelationsHandler->showWith())->findOrFail($id);

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
        $this->serviceRequestIdempotencyHandler->ensureDeviceIdempotency($data['details'] ?? []);

        return DB::transaction(function () use ($data) {
            $serviceRequest = $this->createMainServiceRequest($data);
            $this->createServiceRequestDetails($serviceRequest, $data['details'] ?? []);

            $this->auditLogService->createServiceRequestAuditLog($serviceRequest, 'CREATE_REQUEST', 'Request dibuat');

            $actor = Auth::user();

            $serviceRequestId = $serviceRequest->id;
            $actorName = $actor->name;
            $actorEmail = $actor->email;

            DB::afterCommit(function () use ($serviceRequestId, $actorName, $actorEmail) {
                $this->contactAdminMailService->sendAdminNotification($serviceRequestId, $actorName, $actorEmail);
            });

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

        if (!$user) {
            throw new \RuntimeException('Unauthenticated.');
        }

        $isAdmin = $user->roles->contains('id', Role::ADMIN);
        $isUser = $user->roles->contains('id', Role::USER);

        if ($isAdmin) {
            // Admins can create on behalf of another user.
            $adminId = $user->id;
            $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        } elseif ($isUser) {
            // Users can only create requests for themselves.
            if (isset($data['user_id']) && (int) $data['user_id'] !== (int) $user->id) {
                throw new \InvalidArgumentException('You can only create a service request for your own account.');
            }

            $userId = $user->id;
        } else {
            throw new \InvalidArgumentException('Your role is not allowed to create service requests.');
        }

        return ServiceRequest::create([
            'service_number'   => $this->generateServiceNumber(),
            'admin_id'         => $adminId,
            'user_id'          => $userId,
            'request_date'     => $data['request_date'] ?? now(),
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

            if (isset($data['details'])) {
                $this->serviceRequestIdempotencyHandler->ensureDeviceIdempotency($data['details'], $serviceRequest->id);
            }

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

            StatusHandler::handle($serviceRequest,$status, $serviceRequest->code, $this->notificationService, $this->invoiceService, $this);

            return $this->loadRelations($serviceRequest);
        });
    }

    public function addSolution(int $id, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        
        $serviceRequest->serviceRequestDetails()->update([
            'solution' => $data['solution'],
        ]);
        
        return $this->loadRelations($serviceRequest);
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
        return $serviceRequest->load($this->showRelationsHandler->defaultWith());
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

    public function markDevicesAsBadAsset(ServiceRequest $serviceRequest): void
    {
        $deviceIds = $serviceRequest->service_request_details()
            ->whereNotNull('device_id')
            ->pluck('device_id')
            ->unique()
            ->values();

        if ($deviceIds->isEmpty()) {
            return;
        }

        Device::whereIn('id', $deviceIds)->update(['bad_asset' => true]);
    }
}
