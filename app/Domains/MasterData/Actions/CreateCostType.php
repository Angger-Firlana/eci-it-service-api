<?php

namespace App\Domains\MasterData\Actions;

use App\Models\CostType;

class CreateCostType
{
    public function execute(array $data): CostType
    {
        return CostType::create($data);
    }
}
