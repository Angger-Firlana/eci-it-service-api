<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\VendorApproval;
use Illuminate\Support\Collection;

class InboxApprovalService
{
    public function getInboxApprovals($statusId): Collection
    {
        $inboxs = VendorApproval::with('service_request')
            ->where('approver_id', auth()->id())
            ->where('status_id', $statusId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $inboxs;
    }

    public function readInbox($id)
    {
        $inbox = VendorApproval::find($id);
        $inbox->read_at = now();
        $inbox->save();

        return $inbox;
    }
}
