<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Models\ServiceRequest;
use App\Models\StatusTransition;

class GetAllowedTransitions
{
    public function execute(int $serviceRequestId): array
    {
        $serviceRequest = ServiceRequest::with('status:id,name,code')->findOrFail($serviceRequestId);

        $roleIds = auth()->user()
            ->roles()
            ->pluck('roles.id');

        $transitions = StatusTransition::query()
            ->where('from_status_id', $serviceRequest->status_id)
            ->whereHas('roles', function ($query) use ($roleIds) {
                $query->whereIn('roles.id', $roleIds);
            })
            ->with([
                'status:id,name,code',
                'roles:id,name',
            ])
            ->orderBy('id')
            ->get()
            ->map(function (StatusTransition $transition) {
                return [
                    'id' => $transition->id,
                    'code' => $transition->code,
                    'description' => $transition->description,
                    'to_status' => $transition->status ? [
                        'id' => $transition->status->id,
                        'name' => $transition->status->name,
                        'code' => $transition->status->code,
                    ] : null,
                    'roles' => $transition->roles->map(fn ($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                    ])->values(),
                ];
            })
            ->values();

        return [
            'service_request_id' => $serviceRequest->id,
            'current_status' => $serviceRequest->status ? [
                'id' => $serviceRequest->status->id,
                'name' => $serviceRequest->status->name,
                'code' => $serviceRequest->status->code,
            ] : null,
            'transitions' => $transitions,
        ];
    }
}
