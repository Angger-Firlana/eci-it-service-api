<?php

namespace App\Domains\Inbox\Actions;

use App\Models\VendorApproval;

class ReadInboxApproval
{
    public function execute(int $id): ?VendorApproval
    {
        $inbox = VendorApproval::find($id);
        if (!$inbox) {
            return null;
        }

        $inbox->read_at = now();
        $inbox->save();

        return $inbox;
    }
}
