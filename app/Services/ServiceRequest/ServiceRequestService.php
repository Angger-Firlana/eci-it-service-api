<?php

namespace App\Services\ServiceRequest;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;

use App\Domains\ServiceRequest\Support\ShowRelationsHandler;
use App\Domains\ServiceRequest\Support\StatusHandler;

use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\StatusTransition;
use App\Models\Role;
use App\Models\Device;

use App\Services\ServiceRequest\DetailServiceRequestService;
use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;
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
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;
    protected ShowRelationsHandler $showRelationsHandler;
    protected NotificationService $notificationService;
    protected StatusHandler $statusHandler;

    public function __construct(
        DetailServiceRequestService $detailService,
        ContactAdminMailservice $contactAdminMailService,
        InvoiceService $invoiceService,
        AuditLogService $auditLogService,
        ServiceRequestApprovalService $serviceRequestApprovalService,
        EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest,
        ShowRelationsHandler $showRelationsHandler,
        NotificationService $notificationService,
        StatusHandler $statusHandler
    ) {
        $this->detailService = $detailService;
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
        $this->contactAdminMailService = $contactAdminMailService;
        $this->invoiceService = $invoiceService;
        $this->auditLogService = $auditLogService;
        $this->serviceRequestApprovalService = $serviceRequestApprovalService;
        $this->showRelationsHandler = $showRelationsHandler;
        $this->notificationService = $notificationService;
        $this->statusHandler = $statusHandler;
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

        $auditLogs = $this->auditLogService->getAuditLogsForServiceRequest($serviceRequest);
        $serviceRequest->audit_logs = $auditLogs;
    
        return $serviceRequest;
    }

    public function createServiceRequest(array $data): ServiceRequest
    {
        $this->ensureDeviceIsNotActiveInOtherRequest->execute($data['details'] ?? []);

        return DB::transaction(function () use ($data) {
            $serviceRequest = $this->createMainServiceRequest($data);
            $this->createServiceRequestDetails($serviceRequest, $data['details'] ?? []);

            $this->auditLogService->createServiceRequestAuditLog($serviceRequest, 'CREATE_REQUEST', 'Request dibuat');

            $actor = Auth::user();

            $this->contactAdminMailService->sendAdminNotification($serviceRequest->id, $actor->name, $actor->email);

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
        $operatorId = null;
        $userId = null;
        $user = Auth::user();

        $isOperator = $user->roles->contains('id', Role::OPERATOR);
        $isUser = $user->roles->contains('id', Role::USER);

        if ($isOperator) {
            // Operators can create on behalf of another user.
            $operatorId = $user->id;
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
            'operator_id'         => $operatorId,
            'user_id'          => $userId,
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
    public function loadRelations(ServiceRequest $serviceRequest): ServiceRequest
    {
        return $serviceRequest->load($this->showRelationsHandler->defaultWith());
    }

    //function to sync details
    public function syncDetails(ServiceRequest $serviceRequest, array $details): void
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
