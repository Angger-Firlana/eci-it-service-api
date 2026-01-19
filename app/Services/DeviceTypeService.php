<?php

namespace App\Services;

use App\Models\DeviceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeviceTypeService
{
    public function getAll(?string $search = null): Collection
    {
        return DeviceType::query()
            ->when($search, fn ($q) =>
                $q->where('name', 'LIKE', "%{$search}%")
            )
            ->get();
    }

    public function getById(int $id): DeviceType
    {
        return DeviceType::findOrFail($id);
    }

    public function create(array $data): DeviceType
    {
        return DeviceType::create([
            'name' => $data['name']
        ]);
    }

    public function update(int $id, array $data): DeviceType
    {
        $deviceType = DeviceType::findOrFail($id);

        $deviceType->update([
            'name' => $data['name']
        ]);

        return $deviceType;
    }

    public function delete(int $id): void
    {
        $deviceType = DeviceType::findOrFail($id);
        $deviceType->delete();
    }
}
