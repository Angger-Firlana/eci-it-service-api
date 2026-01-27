<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceType;
use App\Models\Status;
use App\Models\Vendor;
use App\Models\Role;
use App\Models\User;
use App\Helpers\APIResponse;

class ReferenceDataController extends Controller
{
    public function getServiceTypes()
    {
        $serviceTypes = ServiceType::select('id', 'name')->get();
        return APIResponse::success($serviceTypes);
    }

    public function getStatuses(Request $request)
    {
        $query = Status::select('id', 'name', 'code', 'entity_type_id');
        
        if ($request->has('entity_type_id')) {
            $query->where('entity_type_id', $request->entity_type_id);
        }

        $statuses = $query->get();
        return APIResponse::success($statuses);
    }

    public function getVendors()
    {
        $vendors = Vendor::select('id', 'name', 'description')->get();
        return APIResponse::success($vendors);
    }

    public function getDepartments()
    {
        $departments = \App\Models\Department::select('id', 'name', 'code')->get();
        return APIResponse::success($departments);
    }

    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return APIResponse::success($roles);
    }

    public function getUsers()
    {
        $users = User::select('id', 'name', 'email')->get();
        return APIResponse::success($users);
    }

    public function getCostTypes()
    {
        $costTypes = CostType::select('id', 'name')->get();
        return APIResponse::success($costTypes);
    }
}
