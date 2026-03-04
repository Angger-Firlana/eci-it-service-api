<?php

namespace App\Domains\Inbox\Actions;

use App\Models\VendorApproval;
use App\Domains\ServiceRequest\Support\ShowRelationsHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ListInboxApprovals
{
    protected ShowRelationsHandler $showRelationsHandler;

    public function __construct(ShowRelationsHandler $showRelationsHandler)
    {
        $this->showRelationsHandler = $showRelationsHandler;
    }

    public function execute(int $statusId, Request $request): Collection
    {
        $serviceRequestIncludes = $this->resolveServiceRequestIncludes($request);

        $query = VendorApproval::query()
            ->where('approver_id', auth()->id())
            ->where('status_id', $statusId)
            ->orderBy('created_at', 'desc');

        if (!empty($serviceRequestIncludes)) {
            $query->with([
                'service_request' => function ($serviceRequestQuery) use ($serviceRequestIncludes) {
                    $serviceRequestQuery->with($serviceRequestIncludes);
                }
            ]);
        } else {
            $query->with('service_request');
        }

        return $query->get();
    }

    private function resolveServiceRequestIncludes(Request $request): array
    {
        $tokens = $this->showRelationsHandler->parseIncludeTokens($request->get('include', ''));

        foreach ($tokens as $token) {
            if ($token['name'] !== 'serviceRequest') {
                continue;
            }

            $includeString = implode(',', $token['args']);
            return $this->showRelationsHandler->indexWithFromIncludeString($includeString);
        }

        return [];
    }
}
