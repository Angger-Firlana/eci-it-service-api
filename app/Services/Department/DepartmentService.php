<?php 

namespace App\Services\Department;

use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DepartmentService{
    public function getAllDepartment(Request $request){
        $departments = Department::query()->with('users');

        if($request->has('search')){
            $departments->where('name', 'like', "%{$request->search}%");
        }

        if($request->has('sort_by')){
            $departments->orderBy($request->sort_by, $request->sort_order);
        }

        return $departments->paginate($request->per_page);
    }

    public function getById($id):Department{
        $department = Department::findOrFail($id);
        
        return $department;
    }

    public function createDepartment(array $data){
        $department = Department::create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return $department->load('users');
    }

    public function updateDepartment(int $id, array $data){
        $department = Department::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'code' => $data['code'],
        ];

        $department->update($updateData);

        return $department->load('users');
    }

    public function deleteDepartment(int $id){
        $department = Department::findOrFail($id);
        $department->delete();
        return true;
    }
}