<?php

namespace App\Domains\MasterData\Actions;

use App\Models\CostType;

class GetCostTypeById
{
    public function execute(int $id): CostType
    {
        return CostType::findOrFail($id);
    }
}
