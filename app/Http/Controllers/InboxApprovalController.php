<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Inbox\Actions\ListInboxApprovals;
use App\Domains\Inbox\Actions\ReadInboxApproval;
use App\Helpers\APIResponse;

class InboxApprovalController extends Controller
{
    protected ListInboxApprovals $listInboxApprovals;
    protected ReadInboxApproval $readInboxApproval;
    
    public function __construct(
        ListInboxApprovals $listInboxApprovals,
        ReadInboxApproval $readInboxApproval
    )
    {
        $this->listInboxApprovals = $listInboxApprovals;
        $this->readInboxApproval = $readInboxApproval;
    }
    
    public function index($statusId)
    {
        $inboxs = $this->listInboxApprovals->execute((int) $statusId);
        return APIResponse::success($inboxs);
    }
    
    public function readInbox($id)
    {
        $inbox = $this->readInboxApproval->execute((int) $id);
        return APIResponse::success($inbox);
    }
}
