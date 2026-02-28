<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServiceRequest\ServiceRequestService;
use App\Helpers\APIResponse;
use App\Http\Requests\ServiceRequest\StoreServiceRequest;
use App\Domains\ServiceRequest\Services\UpdateServiceRequestWorkflow;
use App\Http\Requests\ServiceRequest\UpdateServiceRequest;

class ServiceRequestController extends Controller
{
    //
    protected $serviceRequestService;
    protected $updateServiceRequestWorkFlow;
    public function __construct(ServiceRequestService $serviceRequestService, UpdateServiceRequestWorkflow $updateServiceRequestWorkFlow){
        $this->serviceRequestService = $serviceRequestService;
        $this->updateServiceRequestWorkFlow = $updateServiceRequestWorkFlow;
    }

    public function index(Request $request){
        $paginator = $this->serviceRequestService->getAllServiceRequest($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "Success", $meta);
    }

    public function show($id){
        $serviceRequests = $this->serviceRequestService->getServiceRequestById($id);
        return APIResponse::success($serviceRequests);
    }

    public function store(StoreServiceRequest $request){
        $serviceRequests = $this->serviceRequestService->createServiceRequest($request->validated());
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
        $serviceRequests = $this->serviceRequestService->deleteServiceRequest($id);
        return APIResponse::success($serviceRequests);
    }

    public function allowedTransitions($id) {
        $transitions = $this->serviceRequestService->getAllowedTransitions($id);
        return APIResponse::success($transitions);
    }

    public function stats() {
        $stats = $this->serviceRequestService->getStats();
        return APIResponse::success($stats);
    }
}
