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
        return ServiceRequest::with($this->showRelationsHandler->indexWith())
            ->filter($request)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
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
}