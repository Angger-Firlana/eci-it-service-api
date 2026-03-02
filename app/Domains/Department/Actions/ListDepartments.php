<?php

namespace App\Domains\Department\Actions;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListDepartments
{
    public function execute(Request $request): LengthAwarePaginator
    {
        $departments = Department::query()->with('users');

        if ($request->has('search')) {
            $departments->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('sort_by')) {
            $departments->orderBy($request->sort_by, $request->sort_order);
        }

        return $departments->paginate($request->per_page);
    }
}
