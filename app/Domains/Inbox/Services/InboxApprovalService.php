<?php

namespace App\Domains\Inbox\Services;

use App\Domains\Inbox\Actions\ListInboxApprovals;
use App\Domains\Inbox\Actions\ReadInboxApproval;
use Illuminate\Support\Collection;

class InboxApprovalService
{
    public function __construct(
        protected ListInboxApprovals $listInboxApprovals,
        protected ReadInboxApproval $readInboxApproval
    ) {
    }

    public function getInboxApprovals(int $statusId): Collection
    {
        return $this->listInboxApprovals->execute($statusId);
    }

    public function readInbox(int $id)
    {
        return $this->readInboxApproval->execute($id);
    }
}
