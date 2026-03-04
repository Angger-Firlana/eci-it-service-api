<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Inbox\Actions\ListInboxApprovals;
use App\Domains\Inbox\Actions\ListInboxApprovalsSummary;
use App\Domains\Inbox\Actions\ReadInboxApproval;
use App\Helpers\APIResponse;

class InboxApprovalController extends Controller
{
    protected ListInboxApprovals $listInboxApprovals;
    protected ListInboxApprovalsSummary $listInboxApprovalsSummary;
    protected ReadInboxApproval $readInboxApproval;
    
    public function __construct(
        ListInboxApprovals $listInboxApprovals,
        ListInboxApprovalsSummary $listInboxApprovalsSummary,
        ReadInboxApproval $readInboxApproval
    )
    {
        $this->listInboxApprovals = $listInboxApprovals;
        $this->listInboxApprovalsSummary = $listInboxApprovalsSummary;
        $this->readInboxApproval = $readInboxApproval;
    }
    
    public function index(Request $request, $statusId)
    {
        $inboxs = $this->listInboxApprovals->execute((int) $statusId, $request);
        return APIResponse::success($inboxs);
    }

    public function summary(Request $request, $statusId)
    {
        $paginator = $this->listInboxApprovalsSummary->execute((int) $statusId, $request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, 'Success', $meta);
    }
    
    public function readInbox($id)
    {
        $inbox = $this->readInboxApproval->execute((int) $id);
        return APIResponse::success($inbox);
    }
}
