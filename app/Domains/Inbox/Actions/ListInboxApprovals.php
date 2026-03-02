<?php

namespace App\Domains\Inbox\Actions;

use App\Models\VendorApproval;
use Illuminate\Support\Collection;

class ListInboxApprovals
{
    public function execute(int $statusId): Collection
    {
        return VendorApproval::with('service_request')
            ->where('approver_id', auth()->id())
            ->where('status_id', $statusId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
