<?php

namespace App\Domains\Department\Actions;

use App\Models\Department;

class DeleteDepartment
{
    public function execute(int $id): void
    {
        $department = Department::findOrFail($id);
        $department->delete();
    }
}
