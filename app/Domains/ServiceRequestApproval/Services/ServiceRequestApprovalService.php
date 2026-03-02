<?php

namespace App\Domains\ServiceRequestApproval\Services;

use App\Domains\ServiceRequestApproval\Actions\CreateVendorApprovals;
use App\Domains\ServiceRequestApproval\Actions\DeleteVendorApproval;
use App\Domains\ServiceRequestApproval\Actions\GetApproverByServiceRequestId;
use App\Domains\ServiceRequestApproval\Actions\GetVendorApprovalById;
use App\Domains\ServiceRequestApproval\Actions\GetVendorApprovalIdsByServiceRequestId;
use App\Domains\ServiceRequestApproval\Actions\ListVendorApprovalsByServiceRequestId;
use App\Domains\ServiceRequestApproval\Actions\UpdateVendorApprovals;
use App\Models\VendorApproval;
use Illuminate\Database\Eloquent\Collection;

class ServiceRequestApprovalService
{
    public function __construct(
        protected UpdateVendorApprovals $updateVendorApprovals,
        protected CreateVendorApprovals $createVendorApprovals,
        protected DeleteVendorApproval $deleteVendorApproval,
        protected GetVendorApprovalById $getVendorApprovalById,
        protected ListVendorApprovalsByServiceRequestId $listVendorApprovalsByServiceRequestId,
        protected GetVendorApprovalIdsByServiceRequestId $getVendorApprovalIdsByServiceRequestId,
        protected GetApproverByServiceRequestId $getApproverByServiceRequestId
    ) {
    }

    public function getApproverByServiceRequestId(int $serviceRequestId): array
    {
        return $this->getApproverByServiceRequestId->execute($serviceRequestId);
    }

    public function update(int $serviceRequestId, array $approvalsData): Collection
    {
        return $this->updateVendorApprovals->execute($serviceRequestId, $approvalsData);
    }

    public function createVendorApprovals(int $serviceRequestId, array $data): Collection
    {
        return $this->createVendorApprovals->execute($serviceRequestId, $data);
    }

    public function destroy(int $serviceRequestId, int $approvalId): bool
    {
        $approval = $this->getApprovalById($approvalId);
        if ((int) $approval->service_request_id !== (int) $serviceRequestId) {
            throw new \Exception('Approval does not belong to this service request');
        }

        $this->deleteVendorApproval($approvalId);

        return true;
    }

    public function deleteVendorApproval(int $id): void
    {
        $this->deleteVendorApproval->execute($id);
    }

    public function getApprovalById(int $id): VendorApproval
    {
        return $this->getVendorApprovalById->execute($id);
    }

    public function getByServiceRequestId(int $serviceRequestId): Collection
    {
        return $this->listVendorApprovalsByServiceRequestId->execute($serviceRequestId);
    }

    public function getIdsByServiceRequestId(int $serviceRequestId): array
    {
        return $this->getVendorApprovalIdsByServiceRequestId->execute($serviceRequestId);
    }
}

