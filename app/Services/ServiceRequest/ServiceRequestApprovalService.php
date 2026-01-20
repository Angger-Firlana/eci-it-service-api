<?php

namespace App\Services\ServiceRequest;

use App\Models\VendorApproval;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class ServiceRequestApprovalService
{
    public function updateVendorApprovals(ServiceRequest $serviceRequest, array $approvals): \Illuminate\Database\Eloquent\Collection
    {
        DB::beginTransaction();
        
        foreach ($approvals as $approvalData) {
            VendorApproval::updateOrCreate(
                [
                    'service_request_id' => $serviceRequest->id,
                    'approver_id' => $approvalData['approver_id'],
                ],
                [
                    'assigned_by' => $approvalData['assigned_by'] ?? auth()->id(),
                    'assigned_at' => $approvalData['assigned_at'] ?? now(),
                    'approved_at' => $approvalData['approved_at'] ?? null,
                    'status' => $approvalData['status'] ?? 'pending',
                    'notes' => $approvalData['notes'] ?? null,
                ]
            );
        }
        
        DB::commit();

        return $serviceRequest->vendorApprovals()->with(['approver', 'assignedBy'])->get();
    }

    public function createVendorApproval(array $data, ServiceRequest $serviceRequest): VendorApproval
    {
        $approval = VendorApproval::create([
            'service_request_id' => $serviceRequest->id,
            'approver_id' => $data['approver_id'],
            'assigned_by' => $data['assigned_by'] ?? auth()->id(),
            'assigned_at' => now(),
            'approved_at' => $data['approved_at'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return $approval->load(['approver', 'assignedBy']);
    }

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status' => 'approved',
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        // Update service request status if all approvals are done
        $this->checkAndUpdateServiceRequestStatus($approval->serviceRequest);

        return $approval->load(['approver', 'assignedBy', 'serviceRequest']);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status' => 'rejected',
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        // Update service request status to rejected
        $approval->serviceRequest->update(['status_id' => 5]); // Assuming 5 is rejected status

        return $approval->load(['approver', 'assignedBy', 'serviceRequest']);
    }

    public function deleteVendorApproval(int $id): void
    {
        $approval = VendorApproval::findOrFail($id);
        $approval->delete();
    }

    public function getApprovalById(int $id): VendorApproval
    {
        $approval = VendorApproval::with(['approver', 'assignedBy', 'serviceRequest'])
            ->findOrFail($id);

        return $approval;
    }

    public function getApprovalsByServiceRequest(int $serviceRequestId): \Illuminate\Database\Eloquent\Collection
    {
        $approvals = VendorApproval::with(['approver', 'assignedBy'])
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $approvals;
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $pendingApprovals = $serviceRequest->vendorApprovals()
            ->where('status', 'pending')
            ->count();
        
        $rejectedApprovals = $serviceRequest->vendorApprovals()
            ->where('status', 'rejected')
            ->count();
        
        if ($rejectedApprovals > 0) {
            $serviceRequest->update(['status_id' => 5]); // Rejected
        } elseif ($pendingApprovals === 0) {
            $serviceRequest->update(['status_id' => 2]); // In Progress
        }
    }
}
