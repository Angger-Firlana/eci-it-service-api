<?php

namespace App\Domains\Approval\Services;

use App\Domains\Approval\Actions\RejectVendorRequest;
use App\Models\VendorApproval;

class RejectVendorRequestWorkflow
{
    public function __construct(
        protected RejectVendorRequest $rejectVendorRequest
    ) {
    }

    public function execute(int $approvalId, array $data): VendorApproval
    {
        return $this->rejectVendorRequest->execute($approvalId, $data);
    }
}
