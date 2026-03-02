<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\ApproveVendorRequest;
use App\Models\VendorApproval;

class ApproveVendorRequestWorkflow
{
    public function __construct(
        protected ApproveVendorRequest $approveVendorRequest
    ) {
    }

    public function execute(int $approvalId, array $data): VendorApproval
    {
        return $this->approveVendorRequest->execute($approvalId, $data);
    }
}
