<?php

namespace App\Domains\Department\Actions;

use App\Models\Department;

class UpdateDepartment
{
    public function execute(int $id, array $data): Department
    {
        $department = Department::findOrFail($id);

        $department->update([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return $department->load('users');
    }
}
