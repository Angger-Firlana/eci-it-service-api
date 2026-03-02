<?php

namespace App\Domains\MasterData\Actions;

use App\Models\CostType;

class DeleteCostType
{
    public function execute(int $id): void
    {
        $costType = CostType::findOrFail($id);
        $costType->delete();
    }
}
