<?php

namespace App\Domains\Department\Services;

use App\Domains\Department\Actions\CreateDepartment;
use App\Domains\Department\Actions\DeleteDepartment;
use App\Domains\Department\Actions\GetDepartmentById;
use App\Domains\Department\Actions\ListDepartments;
use App\Domains\Department\Actions\UpdateDepartment;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class DepartmentService
{
    public function __construct(
        protected ListDepartments $listDepartments,
        protected GetDepartmentById $getDepartmentById,
        protected CreateDepartment $createDepartment,
        protected UpdateDepartment $updateDepartment,
        protected DeleteDepartment $deleteDepartment
    ) {
    }

    public function getAllDepartment(Request $request): LengthAwarePaginator
    {
        return $this->listDepartments->execute($request);
    }

    public function getById(int $id): Department
    {
        return $this->getDepartmentById->execute($id);
    }

    public function createDepartment(array $data): Department
    {
        return $this->createDepartment->execute($data);
    }

    public function updateDepartment(int $id, array $data): Department
    {
        return $this->updateDepartment->execute($id, $data);
    }

    public function deleteDepartment(int $id): void
    {
        $this->deleteDepartment->execute($id);
    }
}
