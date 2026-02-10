<?php

namespace App\Services\MasterData;

use App\Models\CostType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CostTypeService
{
    public function getAllCostTypes(Request $request):Collection{
        $costTypes = CostType::query()->filter($request)->get();
        return $costTypes;
    }

    public function getCostTypeById($id):CostType{
        $costType = CostType::findOrFail($id);
        return $costType;
    }

    public function createCostType(array $data):CostType{
        $costType = CostType::create($data);
        return $costType;
    }
    
    public function updateCostType($id, array $data):CostType{
        $costType = CostType::findOrFail($id);

        if(isset($data['code'])) {
            $costType->code = $data['code'];
        }
        if(isset($data['name'])) {
            $costType->name = $data['name'];
        }
        $costType->save();
        
        return $costType;
    }
    
    public function deleteCostType($id):void{
        $costType = CostType::findOrFail($id);
        $costType->delete();
    }
}
