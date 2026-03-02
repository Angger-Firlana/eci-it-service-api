<?php

namespace App\Domains\Device\Actions;

use App\Models\DeviceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDeviceTypes
{
    public function execute(?string $search = null): LengthAwarePaginator
    {
        return DeviceType::query()
            ->when($search, fn ($q) =>
                $q->where('name', 'LIKE', "%{$search}%")
            )
            ->paginate(15);
    }
}
