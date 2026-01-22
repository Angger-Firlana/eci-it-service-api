<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Helpers\APIResponse;
use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    //
    protected $departmentService;

    public function __construct(DepartmentService $departmentService){
        $this->departmentService = $departmentService;
    }
    public function index(Request $request){
        $paginator = $this->departmentService->getAllDepartment($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);

        return APIResponse::success($data, 200, "", $meta);
    }

    public function store(StoreDepartmentRequest $request){
        $data = $this->departmentService->createDepartment($request->validated());

        return APIResponse::success($data, 201, "");
    }

    public function show($id){
        $data = $this->departmentService->getById($id);

        return APIResponse::success($data, 200, "");
    }

    public function update($id, UpdateDepartmentRequest $request){
        $data = $this->departmentService->updateDepartment($id, $request->validated());

        return APIResponse::success($data, 200, "");
    }

    public function destroy($id){
        $data = $this->departmentService->deleteDepartment($id);

        return APIResponse::success($data, 200, "");
    }
}
