<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestDetail;
use App\Models\Device;
use App\Models\VendorApproval;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceRequest\ServiceRequestApprovalService;
use App\Services\ServiceRequest\DetailServiceRequestService;
use App\Services\ServiceRequest\ServiceLocationService;
use App\Services\ServiceRequest\ServiceRequestCancellationService;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceRequestService
{
    protected $detailService;
    protected $locationService;
    protected $cancellationService;
    protected $approvalService;

    public function __construct(
        DetailServiceRequestService $detailService,
        ServiceLocationService $locationService,
        ServiceRequestCancellationService $cancellationService,
        ServiceRequestApprovalService $approvalService
    ) {
        $this->detailService = $detailService;
        $this->locationService = $locationService;
        $this->cancellationService = $cancellationService;
        $this->approvalService = $approvalService;
    }

    public function getAllServiceRequest(Request $request): LengthAwarePaginator
    {
        $serviceRequests = ServiceRequest::with([
            'user', 
            'admin', 
            'service_type', 
            'status', 
            'details.device', 
            'serviceLocation.vendor', 
            'serviceCosts.costType', 
            'serviceCancellation', 
            'vendorApprovals.approver', 
            'vendorApprovals.assignedBy'
        ])
        ->when($request->has('user_id'), function($query) use ($request) {
            $query->where('user_id', $request->user_id);
        })
        ->when($request->has('admin_id'), function($query) use ($request) {
            $query->where('admin_id', $request->admin_id);
        })
        ->when($request->has('service_type_id'), function($query) use ($request) {
            $query->where('service_type_id', $request->service_type_id);
        })
        ->when($request->has('status_id'), function($query) use ($request) {
            $query->where('status_id', $request->status_id);
        })
        ->when($request->has('request_date'), function($query) use ($request) {
            $query->whereDate('request_date', $request->request_date);
        })
        ->when($request->has('estimated_date'), function($query) use ($request) {
            $query->whereDate('estimated_date', $request->estimated_date);
        })
        ->when($request->has('search'), function($query) use ($request) {
            $query->where('service_number', 'like', '%' . $request->search . '%');
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 15));

        return $serviceRequests;
    }

    public function getServiceRequestById(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::with([
            'user:id,name,email', 
            'admin:id,name,email', 
            'service_type:id,name', 
            'status:id,name', 
            'service_request_details:id,service_request_id,device_id,complaint',
            'service_request_details.device:id,device_model_id,serial_number',
            'service_request_details.device.device_model:id,brand,model', 
            'service_request_details.complaint_images',
            'service_locations.vendor:id,name', 
            'service_costs.cost_type:id,type', 
            'service_cancellations:id,reason', 
            'vendor_approvals:id,service_request_id,approver_id,assigned_by',
            'vendor_approvals.approver:id,name,email', 
            'vendor_approvals.assigned_by:id,name,email'
        ])->findOrFail($id);

        $serviceRequest->makeHidden([
            'created_at',
            'updated_at'
        ]);

        return $serviceRequest;
    }

    public function createServiceRequest(array $data): ServiceRequest
    {
        DB::beginTransaction();
        try {
            $serviceRequest = ServiceRequest::create([
                'service_number' => $this->generateServiceNumber(),
                'admin_id' => $data['admin_id'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'service_type_id' => $data['service_type_id'],
                'request_date' => now(),
                'estimated_date' => $data['estimated_date'] ?? null,
                'status_id' => $data['status_id'] ?? 1, // Default to pending status
            ]);

            // Create service request details
            if (isset($data['details'])) {
                foreach ($data['details'] as $detail) {
                    $this->detailService->createDetailServiceRequest([
                        'service_request_id' => $serviceRequest->id,
                        'device_id' => $detail['device_id'],
                        'complaint' => $detail['complaint'],
                        'complaint_images' => $detail['complaint_images'] ?? [],
                    ]);
                }
            }

            // Create service location
            if (isset($data['service_location'])) {
                $this->locationService->createServiceLocation($data['service_location'], $serviceRequest);
            }

            DB::commit();

            return $serviceRequest->load([
                    'user', 
                    'service_type', 
                    'status', 
                    'service_request_details.device'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateServiceRequest(int $id, array $data): ServiceRequest
    {
        DB::beginTransaction();
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            
            // Update main service request
            $status = Status::findOrFail($data['status_id'] ?? $serviceRequest->status_id);
            
            if($status->entity_type_id != 1){
                throw new \Exception('Status tidak valid');
            }

            $updateData = [
                'admin_id' => $data['admin_id'] ?? $serviceRequest->admin_id,
                'service_type_id' => $data['service_type_id'] ?? $serviceRequest->service_type_id,
                'estimated_date' => $data['estimated_date'] ?? $serviceRequest->estimated_date,
                'status_id' => $data['status_id'] ?? $serviceRequest->status_id,
            ];
            
            
            $serviceRequest->update(array_filter($updateData));

            // Update service request details
            if (isset($data['details'])) {
                foreach ($data['details'] as $detail) {
                    if (isset($detail['id'])) {
                        $this->detailService->updateDetailServiceRequest($detail['id'], $detail);
                    } else {
                        $this->detailService->createDetailServiceRequest([
                            'service_request_id' => $serviceRequest->id,
                            'device_id' => $detail['device_id'],
                            'complaint' => $detail['complaint'],
                            'complaint_images' => $detail['complaint_images'] ?? [],
                        ]);
                    }
                }
            }

            // Update service location
            if (isset($data['service_location'])) {
                if ($serviceRequest->serviceLocation) {
                    $this->locationService->updateServiceLocation($serviceRequest->serviceLocation, $data['service_location']);
                } else {
                    $this->locationService->createServiceLocation($data['service_location'], $serviceRequest);
                }
            }

            // Handle service cancellation
            if (isset($data['service_cancellation'])) {
                $this->cancellationService->createCancellation($data['service_cancellation'], $serviceRequest);
            }

            // Handle vendor approvals
            if (isset($data['vendor_approvals'])) {
                $this->approvalService->updateVendorApprovals($serviceRequest, $data['vendor_approvals']);
            }

            DB::commit();

            return $serviceRequest->load([
                'user', 
                'admin', 
                'service_type', 
                'status', 
                'service_request_details.device', 
                'service_locations.vendor', 
                'service_costs.cost_type', 
                'service_cancellations', 
                'vendor_approvals.approver', 
                'vendor_approvals.assigned_by'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteServiceRequest(int $id): ServiceRequest
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            
            if ($serviceRequest->status_id == 3) { 
                throw new \Exception('Cannot delete completed service request');
            }

            $serviceRequest->delete();

            return $serviceRequest;
        } catch (\Exception $e) {
            throw $e;
        }
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
}
