<?php

namespace App\Domains\Department\Actions;

use App\Models\Department;

class GetDepartmentById
{
    public function execute(int $id): Department
    {
        return Department::findOrFail($id);
    }
}
