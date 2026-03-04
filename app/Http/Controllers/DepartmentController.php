<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Helpers\APIResponse;
use App\Domains\Department\Actions\CreateDepartment;
use App\Domains\Department\Actions\DeleteDepartment;
use App\Domains\Department\Actions\GetDepartmentById;
use App\Domains\Department\Actions\ListDepartments;
use App\Domains\Department\Actions\UpdateDepartment;

class DepartmentController extends Controller
{
    protected ListDepartments $listDepartments;
    protected GetDepartmentById $getDepartmentById;
    protected CreateDepartment $createDepartment;
    protected UpdateDepartment $updateDepartment;
    protected DeleteDepartment $deleteDepartment;

    public function __construct(
        ListDepartments $listDepartments,
        GetDepartmentById $getDepartmentById,
        CreateDepartment $createDepartment,
        UpdateDepartment $updateDepartment,
        DeleteDepartment $deleteDepartment
    ){
        $this->listDepartments = $listDepartments;
        $this->getDepartmentById = $getDepartmentById;
        $this->createDepartment = $createDepartment;
        $this->updateDepartment = $updateDepartment;
        $this->deleteDepartment = $deleteDepartment;
    }
    public function index(Request $request){
        $paginator = $this->listDepartments->execute($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);

        return APIResponse::success($data, 200, "", $meta);
    }

    public function store(StoreDepartmentRequest $request){
        $data = $this->createDepartment->execute($request->validated());

        return APIResponse::success($data, 201, "");
    }

    public function show($id){
        $data = $this->getDepartmentById->execute((int) $id);

        return APIResponse::success($data, 200, "");
    }

    public function update($id, UpdateDepartmentRequest $request){
        $data = $this->updateDepartment->execute((int) $id, $request->validated());

        return APIResponse::success($data, 200, "");
    }

    public function destroy($id){
        $this->deleteDepartment->execute((int) $id);

        return APIResponse::success(null, 200, "");
    }
}
