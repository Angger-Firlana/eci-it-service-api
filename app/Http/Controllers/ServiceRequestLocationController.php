<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ServiceRequest\ServiceLocationService;
use App\Models\ServiceRequest;
use App\Http\Requests\ServiceLocation\UpdateServiceLocationRequest;
use App\Http\Requests\ServiceLocation\StoreServiceLocationRequest;
class ServiceRequestLocationController extends Controller
{
    protected $locationService;

    public function __construct(ServiceLocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function store(StoreServiceLocationRequest $request, $serviceRequestId)
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        
        $location = $serviceRequest->service_locations()->where('is_active', true)->first();

        if ($location) {
             $updatedLocation = $this->locationService->updateServiceLocation($serviceRequestId, $location->id, $request->validated());
             return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
        } else {
             $newLocation = $this->locationService->createServiceLocation($serviceRequestId, $request->validated());
             return APIResponse::success($newLocation, 201, 'Service location set successfully');
        }
    }

    public function index($serviceRequestId)
    {
        $locations = $this->locationService->getLocationsByServiceRequestId($serviceRequestId);
        return APIResponse::success($locations, 200, 'Service locations retrieved successfully');
    }

    public function show($serviceRequestId, $locationId){
        $location = $this->locationService->getLocationById($locationId);
        return APIResponse::success($location, 200, 'Service location retrieved successfully');
    }

    public function update(UpdateServiceLocationRequest $request, $serviceRequestId, $locationId)
    {
        $location = $this->locationService->getLocationById($locationId);
        
        if($location->service_request_id != $serviceRequestId){
            return APIResponse::error('Location does not belong to this service request', 400);
        }

        $updatedLocation = $this->locationService->updateServiceLocation($serviceRequestId, $location->id, $request->validated());
        return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
    }
    
    public function destroy($serviceRequestId, $locationId)
    {
        $this->locationService->deleteServiceLocation($locationId);
        return APIResponse::success(null, 200, 'Service location deleted successfully');
    }
}