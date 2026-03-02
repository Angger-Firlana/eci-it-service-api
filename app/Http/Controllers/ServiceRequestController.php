<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Http\Requests\ServiceRequest\StoreServiceRequest;
use App\Domains\ServiceRequest\Services\UpdateServiceRequestWorkflow;
use App\Domains\ServiceRequest\Services\CreateServiceRequestWorkflow;
use App\Domains\ServiceRequest\Services\GetServiceRequestWorkflow;
use App\Domains\ServiceRequest\Services\DeleteServiceRequestWorkflow;
use App\Http\Requests\ServiceRequest\UpdateServiceRequest;

class ServiceRequestController extends Controller
{
    //
    protected $deleteServiceRequestWorkflow;
    protected $updateServiceRequestWorkFlow;
    protected $createServiceRequestWorkflow;
    protected $getServiceRequestWorkflow;
    public function __construct(CreateServiceRequestWorkflow $createServiceRequestWorkflow, UpdateServiceRequestWorkflow $updateServiceRequestWorkFlow, GetServiceRequestWorkflow $getServiceRequestWorkflow, DeleteServiceRequestWorkflow $deleteServiceRequestWorkflow){
        $this->getServiceRequestWorkflow = $getServiceRequestWorkflow;
        $this->updateServiceRequestWorkFlow = $updateServiceRequestWorkFlow;
        $this->createServiceRequestWorkflow = $createServiceRequestWorkflow;
        $this->deleteServiceReqeustWorkflow = $deleteServiceRequestWorkflow;
    }

    public function index(Request $request){
        $paginator = $this->getServiceRequestWorkflow->getAllServiceRequest($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "Success", $meta);
    }

    public function show($id){
        $serviceRequests = $this->getServiceRequestWorkflow->getServiceRequestById($id);
        return APIResponse::success($serviceRequests);
    }

    public function store(StoreServiceRequest $request){
        $serviceRequests = $this->createServiceRequestWorkflow->execute($request->validated());
        return APIResponse::success($serviceRequests);
    }

    public function update(UpdateServiceRequest $request, $id){
        $dataDto = new \App\Domains\ServiceRequest\DTOs\UpdateServiceRequestData(
            $request->input('details', []),
            $request->input('status_id', null),
            $request->input('operator_id', null),
            $request->input('log_notes', null)
        );
        $serviceRequests = $this->updateServiceRequestWorkFlow->execute($id, $dataDto);
        return APIResponse::success($serviceRequests);
    }

    public function destroy($id){
        $serviceRequests = $this->deleteServiceRequestWorkflow->execute($id);
        return APIResponse::success($serviceRequests);
    }

    public function allowedTransitions($id) {

    }

    public function stats() {
        $stats = $this->getServiceRequestWorkflow->getStats();
        return APIResponse::success($stats);
    }
}
