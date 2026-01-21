<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ServiceRequest\ServiceLocationService;
use App\Models\ServiceRequest;

class ServiceRequestLocationController extends Controller
{
    protected $locationService;

    public function __construct(ServiceLocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'location_type' => 'required|in:internal,external',
            'vendor_id' => 'required_if:location_type,external|exists:vendors,id',
            'is_active' => 'boolean'
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        
        // If location exists, update it? Or just create new? 
        // Logic in service: createServiceLocation just creates. 
        // If we want to replace, we check if it exists.
        
        $location = $serviceRequest->service_locations()->where('is_active', true)->first();

        if ($location) {
             $updatedLocation = $this->locationService->updateServiceLocation($location, $request->all());
             return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
        } else {
             $newLocation = $this->locationService->createServiceLocation($request->all(), $serviceRequest);
             return APIResponse::success($newLocation, 201, 'Service location set successfully');
        }
    }

    public function update(Request $request, $id, $locationId)
    {
        $request->validate([
            'location_type' => 'sometimes|in:internal,external',
            'vendor_id' => 'required_if:location_type,external|exists:vendors,id',
            'is_active' => 'boolean'
        ]);

        $location = $this->locationService->getLocationById($locationId);
        
        if($location->service_request_id != $id){
            return APIResponse::error('Location does not belong to this service request', 400);
        }

        $updatedLocation = $this->locationService->updateServiceLocation($location, $request->all());
        return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
    }
}
