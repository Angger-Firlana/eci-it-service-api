<?php

namespace App\Domains\StatusTransition\Actions;

use App\Models\StatusTransition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListStatusTransitions
{
    public function execute(Request $request): LengthAwarePaginator
    {
        $query = StatusTransition::query()->with(['status', 'roles']);

        if ($request->has('from_status_id')) {
            $query->where('from_status_id', $request->from_status_id);
        }

        if ($request->has('to_status_id')) {
            $query->where('to_status_id', $request->to_status_id);
        }

        if ($request->has('code')) {
            $query->where('code', $request->code);
        }

        return $query->paginate($request->get('per_page', 15));
    }
}
