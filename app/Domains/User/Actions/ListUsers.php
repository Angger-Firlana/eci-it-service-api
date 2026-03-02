<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListUsers
{
    public function execute(Request $request): LengthAwarePaginator
    {
        $users = User::query()->with('departments', 'roles:id,name');

        if ($request->has('search')) {
            $users->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('role_id')) {
            $users->whereHas('roles', function ($query) use ($request) {
                $query->where('roles.id', $request->role_id);
            });
        }

        if ($request->has('department_id')) {
            $users->whereHas('departments', function ($query) use ($request) {
                $query->where('departments.id', $request->department_id);
            });
        }

        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $users->where('is_active', $isActive);
            }
        } elseif ($request->has('status')) {
            $isActive = filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $users->where('is_active', $isActive);
            }
        }

        if ($request->has('sort_by')) {
            $users->orderBy($request->sort_by, $request->sort_order);
        }

        return $users->paginate($request->per_page);
    }
}
