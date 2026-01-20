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
                    'assigned_at' => now(),
                    'approved_at' => $approvalData['approved_at'] ?? null,
                    'status_id' => $approvalData['status_id'] ?? 6,
                    'notes' => $approvalData['notes'] ?? null,
                    'approval_policy_id' => $approvalData['approval_policy_id'] ?? null,
                    'approval_policy_step_id' => $approvalData['approval_policy_step_id'] ?? null,
                ]
            );
        }
        
        DB::commit();

        return $serviceRequest->vendor_approvals()->with(['approver', 'assigned_by'])->get();
    }

    public function createVendorApproval(array $data, ServiceRequest $serviceRequest): VendorApproval
    {
        $approval = VendorApproval::create([
            'service_request_id' => $serviceRequest->id,
            'approver_id' => $data['approver_id'],
            'assigned_by' => $data['assigned_by'] ?? auth()->id(),
            'assigned_at' => now(),
            'approved_at' => $data['approved_at'] ?? null,
            'status_id' => $data['status_id'] ?? 8,
            'notes' => $data['notes'] ?? null,
            'approval_policy_id' => $data['approval_policy_id'] ?? null,
            'approval_policy_step_id' => $data['approval_policy_step_id'] ?? null,
        ]);

        return $approval->load(['approver', 'assigned_by']);
    }

    public function approveVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => 9,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        // Update service request status if all approvals are done
        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    public function rejectVendorRequest(int $approvalId, array $data): VendorApproval
    {
        $approval = VendorApproval::findOrFail($approvalId);
        
        $approval->update([
            'approved_at' => now(),
            'status_id' => 10,
            'notes' => $data['notes'] ?? $approval->notes,
        ]);

        $this->checkAndUpdateServiceRequestStatus($approval->service_request);

        return $approval->load(['approver', 'assigned_by', 'service_request']);
    }

    public function deleteVendorApproval(int $id): void
    {
        $approval = VendorApproval::findOrFail($id);
        $approval->delete();
    }

    public function getApprovalById(int $id): VendorApproval
    {
        $approval = VendorApproval::with(['approver', 'assigned_by', 'service_request'])
            ->findOrFail($id);

        return $approval;
    }

    public function getApprovalsByServiceRequest(int $serviceRequestId): \Illuminate\Database\Eloquent\Collection
    {
        $approvals = VendorApproval::with(['approver', 'assigned_by'])
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $approvals;
    }

    private function checkAndUpdateServiceRequestStatus(ServiceRequest $serviceRequest): void
    {
        $pendingApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', 8)
            ->count();
        
        $rejectedApprovals = $serviceRequest->vendor_approvals()
            ->where('status_id', 10)
            ->count();
        
        if ($rejectedApprovals > 0) {
            $serviceRequest->update(['status_id' => 3]); // Rejected
        } elseif ($pendingApprovals === 0) {
            $serviceRequest->update(['status_id' => 5]); // In Progress
        }
    }
}
