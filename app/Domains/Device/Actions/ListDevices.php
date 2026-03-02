<?php

namespace App\Domains\Device\Actions;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDevices
{
    public function execute(Request $request): LengthAwarePaginator
    {
        $devices = Device::select(['id', 'device_model_id', 'serial_number', 'bad_asset'])
            ->with(['device_model:id,brand,model']);

        if ($request->has('serial-number')) {
            $devices = $devices->where('serial_number', $request->input('serial-number'));
        }

        if ($request->has('brand')) {
            $devices = $devices->whereHas('device_model', function ($query) use ($request) {
                $query->where('brand', $request->input('brand'));
            });
        }

        if ($request->has('model')) {
            $devices = $devices->whereHas('device_model', function ($query) use ($request) {
                $query->where('model', 'like', '%' . $request->input('model') . '%');
            });
        }

        if ($request->has('bad_asset')) {
            $badAsset = filter_var($request->input('bad_asset'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($badAsset !== null) {
                $devices = $devices->where('bad_asset', $badAsset);
            }
        }

        return $devices->paginate($request->get('per_page', 15));
    }
}
