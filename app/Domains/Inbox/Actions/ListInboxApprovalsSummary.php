<?php

namespace App\Domains\Inbox\Actions;

use App\Models\VendorApproval;
use App\Domains\ServiceRequest\Support\ShowRelationsHandler;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListInboxApprovalsSummary
{
    protected ShowRelationsHandler $showRelationsHandler;

    public function __construct(ShowRelationsHandler $showRelationsHandler)
    {
        $this->showRelationsHandler = $showRelationsHandler;
    }

    public function execute(int $statusId, Request $request): LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($request);

        return VendorApproval::query()
            ->select([
                'vendor_approvals.id',
                'vendor_approvals.service_request_id',
                'vendor_approvals.approver_id',
                'vendor_approvals.assigned_at',
                'vendor_approvals.approved_at',
                'vendor_approvals.status_id',
                'vendor_approvals.created_at',
            ])
            ->with([
                'service_request' => function ($serviceRequestQuery) {
                    $serviceRequestQuery
                        ->select([
                            'service_requests.id',
                            'service_requests.user_id',
                            'service_requests.operator_id',
                            'service_requests.status_id',
                            'service_requests.service_number',
                            'service_requests.estimated_date',
                            'service_requests.created_at',
                        ])
                        ->with($this->showRelationsHandler->summaryWith());
                }
            ])
            ->where('vendor_approvals.approver_id', auth()->id())
            ->where('vendor_approvals.status_id', $statusId)
            ->orderBy('vendor_approvals.created_at', 'desc')
            ->paginate($perPage);
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }

        return min($perPage, 100);
    }
}
