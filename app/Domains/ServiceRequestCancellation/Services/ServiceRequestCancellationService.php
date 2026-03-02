<?php

namespace App\Domains\ServiceRequestCancellation\Services;

use App\Domains\ServiceRequestCancellation\Actions\CreateCancellation;
use App\Domains\ServiceRequestCancellation\Actions\DeleteCancellation;
use App\Domains\ServiceRequestCancellation\Actions\GetCancellationById;
use App\Domains\ServiceRequestCancellation\Actions\ListCancellationsByServiceRequestId;
use App\Domains\ServiceRequestCancellation\Actions\UpdateCancellation;
use App\Models\ServiceCancellation;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Collection;

class ServiceRequestCancellationService
{
    public function __construct(
        protected CreateCancellation $createCancellation,
        protected UpdateCancellation $updateCancellation,
        protected DeleteCancellation $deleteCancellation,
        protected GetCancellationById $getCancellationById,
        protected ListCancellationsByServiceRequestId $listCancellationsByServiceRequestId
    ) {
    }

    public function createCancellation(array $data, ServiceRequest $serviceRequest): ServiceCancellation
    {
        return $this->createCancellation->execute($data, $serviceRequest);
    }

    // Kept for backward compatibility with controller arg order.
    public function updateCancellation(array $data, int $id): ServiceCancellation
    {
        return $this->updateCancellation->execute($id, $data);
    }

    public function deleteCancellation(int $id): void
    {
        $this->deleteCancellation->execute($id);
    }

    public function getCancellationById(int $id): ServiceCancellation
    {
        return $this->getCancellationById->execute($id);
    }

    // Kept for backward compatibility with controller method name.
    public function getCancellationByServiceRequest(int $serviceRequestId): Collection
    {
        return $this->getCancellationsByServiceRequest($serviceRequestId);
    }

    public function getCancellationsByServiceRequest(int $serviceRequestId): Collection
    {
        return $this->listCancellationsByServiceRequestId->execute($serviceRequestId);
    }
}

