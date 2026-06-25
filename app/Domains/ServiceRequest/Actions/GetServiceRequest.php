<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use App\Domains\ServiceRequest\Support\ShowRelationsHandler;
use App\Domains\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class GetServiceRequest{

    protected ShowRelationsHandler $showRelationsHandler;
    protected AuditLogService $auditLogService;

    public function __construct(ShowRelationsHandler $showRelationsHandler, AuditLogService $auditLogService)
    {
        $this->showRelationsHandler = $showRelationsHandler;
        $this->auditLogService = $auditLogService;
    }

    public function getServiceRequestById(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::with($this->showRelationsHandler->showWith())->findOrFail($id);

        $auditLogs = $this->auditLogService->getAuditLogsForServiceRequest($serviceRequest);
        $serviceRequest->audit_logs = $auditLogs;
    
        return $serviceRequest;
    
    }

    public function getAllServiceRequest(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($request);
        $currentUser = auth()->user();

        $serviceRequests = ServiceRequest::with($this->showRelationsHandler->indexWith($request))
            ->filter($request)
            ->orderBy('created_at', 'desc');

        if ($currentUser->roles()->where('name', 'user')->exists()) {
            $serviceRequests = $serviceRequests->where('user_id', $currentUser->id);
        }

        $serviceRequests = $serviceRequests->paginate($perPage);

        return $serviceRequests;
    }

    public function getAllServiceRequestSummary(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($request);

        return ServiceRequest::query()
            ->select([
                'service_requests.id',
                'service_requests.user_id',
                'service_requests.operator_id',
                'service_requests.status_id',
                'service_requests.service_number',
                'service_requests.estimated_date',
                'service_requests.created_at',
            ])
            ->with($this->showRelationsHandler->summaryWith())
            ->filter($request)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getStats():array{
        return [
            'total' => ServiceRequest::count(),
            'by_status' => ServiceRequest::select('status_id', DB::raw('count(*) as count'))
                ->with('status:id,name,code')
                ->groupBy('status_id')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status->name,
                        'code' => $item->status->code,
                        'count' => $item->count
                    ];
                }),
            'recent' => ServiceRequest::orderBy('created_at', 'desc')->take(5)->get()
        ];
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
