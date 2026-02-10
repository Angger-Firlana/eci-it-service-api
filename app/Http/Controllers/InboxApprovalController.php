<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Inbox\InboxApprovalService;
use App\Helpers\APIResponse;

class InboxApprovalController extends Controller
{
    protected $inboxApprovalService;
    
    public function __construct(InboxApprovalService $inboxApprovalService)
    {
        $this->inboxApprovalService = $inboxApprovalService;
    }
    
    public function index($statusId)
    {
        $inboxs = $this->inboxApprovalService->getInboxApprovals($statusId);
        return APIResponse::success($inboxs);
    }
    
    public function readInbox($id)
    {
        $inbox = $this->inboxApprovalService->readInbox($id);
        return APIResponse::success($inbox);
    }
}
