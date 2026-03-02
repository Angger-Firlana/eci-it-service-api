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
use App\Domains\AuditLog\Services\AuditLogService;
use App\Domains\Invoice\Services\InvoiceService;
use App\Services\ContactAdmin\ContactAdminMailservice;
use App\Domains\Notification\Services\NotificationService;
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

    

    //function to load relations 
    

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
