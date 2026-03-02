<?php

namespace App\Domains\ServiceRequestCost\Services;

use App\Domains\ServiceRequestCost\Actions\AddCost;
use App\Domains\ServiceRequestCost\Actions\GetAttachment;
use App\Domains\ServiceRequestCost\Actions\ListCostsByRequest;
use App\Domains\ServiceRequestCost\Actions\RemoveCost;
use App\Domains\ServiceRequestCost\Actions\UpdateCost;
use App\Models\ServiceCost;
use Illuminate\Database\Eloquent\Collection;

class ServiceRequestCostService
{
    public function __construct(
        protected AddCost $addCost,
        protected UpdateCost $updateCost,
        protected RemoveCost $removeCost,
        protected ListCostsByRequest $listCostsByRequest,
        protected GetAttachment $getAttachment
    ) {
    }

    public function addCost(int $serviceRequestId, array $data): ServiceCost
    {
        return $this->addCost->execute($serviceRequestId, $data);
    }

    public function updateCost(int $serviceRequestId, int $costId, array $data): ServiceCost
    {
        return $this->updateCost->execute($serviceRequestId, $costId, $data);
    }

    public function removeCost(int $serviceRequestId, int $costId): void
    {
        $this->removeCost->execute($serviceRequestId, $costId);
    }

    public function getCostsByRequest(int $serviceRequestId): Collection
    {
        return $this->listCostsByRequest->execute($serviceRequestId);
    }

    public function getAttachment(int $serviceRequestId, int $costId)
    {
        return $this->getAttachment->execute($serviceRequestId, $costId);
    }
}

