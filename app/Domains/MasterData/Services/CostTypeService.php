<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Actions\CreateCostType;
use App\Domains\MasterData\Actions\DeleteCostType;
use App\Domains\MasterData\Actions\GetCostTypeById;
use App\Domains\MasterData\Actions\ListCostTypes;
use App\Domains\MasterData\Actions\UpdateCostType;
use App\Models\CostType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CostTypeService
{
    public function __construct(
        protected ListCostTypes $listCostTypes,
        protected GetCostTypeById $getCostTypeById,
        protected CreateCostType $createCostType,
        protected UpdateCostType $updateCostType,
        protected DeleteCostType $deleteCostType
    ) {
    }

    public function getAllCostTypes(Request $request): Collection
    {
        return $this->listCostTypes->execute($request);
    }

    public function getCostTypeById(int $id): CostType
    {
        return $this->getCostTypeById->execute($id);
    }

    public function createCostType(array $data): CostType
    {
        return $this->createCostType->execute($data);
    }

    public function updateCostType(int $id, array $data): CostType
    {
        return $this->updateCostType->execute($id, $data);
    }

    public function deleteCostType(int $id): void
    {
        $this->deleteCostType->execute($id);
    }
}
