<?php

namespace App\Domains\MasterData\Actions;

use App\Models\CostType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ListCostTypes
{
    public function execute(Request $request): Collection
    {
        return CostType::query()->filter($request)->get();
    }
}
