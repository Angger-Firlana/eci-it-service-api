<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestLocation\Actions\CreateServiceLocation;
use App\Domains\ServiceRequestLocation\Actions\DeleteServiceLocation;
use App\Domains\ServiceRequestLocation\Actions\GetServiceLocationById;
use App\Domains\ServiceRequestLocation\Actions\ListServiceLocationsByServiceRequestId;
use App\Domains\ServiceRequestLocation\Actions\UpdateServiceLocation;
use App\Models\ServiceRequest;
use App\Http\Requests\ServiceLocation\UpdateServiceLocationRequest;
use App\Http\Requests\ServiceLocation\StoreServiceLocationRequest;
class ServiceRequestLocationController extends Controller
{
    protected CreateServiceLocation $createServiceLocation;
    protected UpdateServiceLocation $updateServiceLocation;
    protected DeleteServiceLocation $deleteServiceLocation;
    protected ListServiceLocationsByServiceRequestId $listServiceLocationsByServiceRequestId;
    protected GetServiceLocationById $getServiceLocationById;

    public function __construct(
        CreateServiceLocation $createServiceLocation,
        UpdateServiceLocation $updateServiceLocation,
        DeleteServiceLocation $deleteServiceLocation,
        ListServiceLocationsByServiceRequestId $listServiceLocationsByServiceRequestId,
        GetServiceLocationById $getServiceLocationById
    )
    {
        $this->createServiceLocation = $createServiceLocation;
        $this->updateServiceLocation = $updateServiceLocation;
        $this->deleteServiceLocation = $deleteServiceLocation;
        $this->listServiceLocationsByServiceRequestId = $listServiceLocationsByServiceRequestId;
        $this->getServiceLocationById = $getServiceLocationById;
    }

    public function store(StoreServiceLocationRequest $request, $serviceRequestId)
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        
        $location = $serviceRequest->service_locations()->where('is_active', true)->first();

        if ($location) {
             $updatedLocation = $this->updateServiceLocation->execute((int) $serviceRequestId, (int) $location->id, $request->validated());
             return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
        } else {
             $newLocation = $this->createServiceLocation->execute((int) $serviceRequestId, $request->validated());
             return APIResponse::success($newLocation, 201, 'Service location set successfully');
        }
    }

    public function index($serviceRequestId)
    {
        $locations = $this->listServiceLocationsByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($locations, 200, 'Service locations retrieved successfully');
    }

    public function show($serviceRequestId, $locationId){
        $location = $this->getServiceLocationById->execute((int) $locationId);
        return APIResponse::success($location, 200, 'Service location retrieved successfully');
    }

    public function update(UpdateServiceLocationRequest $request, $serviceRequestId, $locationId)
    {
        $location = $this->getServiceLocationById->execute((int) $locationId);
        
        if($location->service_request_id != $serviceRequestId){
            return APIResponse::error(null, 400, 'Location does not belong to this service request');
        }

        $updatedLocation = $this->updateServiceLocation->execute((int) $serviceRequestId, (int) $location->id, $request->validated());
        return APIResponse::success($updatedLocation, 200, 'Service location updated successfully');
    }
    
    public function destroy($serviceRequestId, $locationId)
    {
        $this->deleteServiceLocation->execute((int) $locationId);
        return APIResponse::success(null, 200, 'Service location deleted successfully');
    }
}
