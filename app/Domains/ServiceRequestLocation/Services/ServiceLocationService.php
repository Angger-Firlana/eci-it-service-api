<?php

namespace App\Domains\ServiceRequestLocation\Services;

use App\Domains\ServiceRequestLocation\Actions\CreateServiceLocation;
use App\Domains\ServiceRequestLocation\Actions\DeleteServiceLocation;
use App\Domains\ServiceRequestLocation\Actions\GetServiceLocationById;
use App\Domains\ServiceRequestLocation\Actions\ListServiceLocationsByServiceRequestId;
use App\Domains\ServiceRequestLocation\Actions\UpdateServiceLocation;
use App\Models\ServiceLocation;
use Illuminate\Database\Eloquent\Collection;

class ServiceLocationService
{
    public function __construct(
        protected CreateServiceLocation $createServiceLocation,
        protected UpdateServiceLocation $updateServiceLocation,
        protected DeleteServiceLocation $deleteServiceLocation,
        protected ListServiceLocationsByServiceRequestId $listServiceLocationsByServiceRequestId,
        protected GetServiceLocationById $getServiceLocationById
    ) {
    }

    public function createServiceLocation(int $serviceRequestId, array $data): ServiceLocation
    {
        return $this->createServiceLocation->execute($serviceRequestId, $data);
    }

    public function updateServiceLocation(int $serviceRequestId, int $locationId, array $data): ServiceLocation
    {
        return $this->updateServiceLocation->execute($serviceRequestId, $locationId, $data);
    }

    public function deleteServiceLocation(int $id): void
    {
        $this->deleteServiceLocation->execute($id);
    }

    public function getLocationsByServiceRequestId(int $serviceRequestId): Collection
    {
        return $this->listServiceLocationsByServiceRequestId->execute($serviceRequestId);
    }

    public function getLocationById(int $id): ServiceLocation
    {
        return $this->getServiceLocationById->execute($id);
    }
}

