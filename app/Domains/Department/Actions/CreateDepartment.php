<?php

namespace App\Domains\Department\Actions;

use App\Models\Department;

class CreateDepartment
{
    public function execute(array $data): Department
    {
        $department = Department::create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return $department->load('users');
    }
}
