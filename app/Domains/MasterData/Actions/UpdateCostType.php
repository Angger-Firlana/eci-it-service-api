<?php

namespace App\Domains\MasterData\Actions;

use App\Models\CostType;

class UpdateCostType
{
    public function execute(int $id, array $data): CostType
    {
        $costType = CostType::findOrFail($id);

        if (isset($data['code'])) {
            $costType->code = $data['code'];
        }
        if (isset($data['name'])) {
            $costType->name = $data['name'];
        }

        $costType->save();

        return $costType;
    }
}
