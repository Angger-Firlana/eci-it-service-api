<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServiceRequest\ServiceRequestService;
use App\Helpers\APIResponse;
use App\Http\Requests\ServiceRequest\StoreServiceRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequest;

class ServiceRequestController extends Controller
{
    //
    protected $serviceRequestService;
    public function __construct(ServiceRequestService $serviceRequestService){
        $this->serviceRequestService = $serviceRequestService;
    }

    public function index(Request $request){
        $serviceRequests = $this->serviceRequestService->getAllServiceRequest($request);
        return APIResponse::success($serviceRequests);
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
        $serviceRequests = $this->serviceRequestService->updateServiceRequest($id, $request->validated());
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
