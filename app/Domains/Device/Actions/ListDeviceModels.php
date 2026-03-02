<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDeviceModels
{
    public function execute(?string $keyword): LengthAwarePaginator
    {
        return DeviceModel::query()
            ->when($keyword, fn ($q) => $q->where('model', 'LIKE', "%{$keyword}%"))
            ->paginate(15);
    }
}
