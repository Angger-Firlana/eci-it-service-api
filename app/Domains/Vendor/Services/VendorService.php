<?php

namespace App\Domains\Vendor\Services;

use App\Domains\Vendor\Actions\CreateVendor;
use App\Domains\Vendor\Actions\DeleteVendor;
use App\Domains\Vendor\Actions\GetVendorById;
use App\Domains\Vendor\Actions\ListVendors;
use App\Domains\Vendor\Actions\UpdateVendor;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class VendorService
{
    public function __construct(
        protected ListVendors $listVendors,
        protected GetVendorById $getVendorById,
        protected CreateVendor $createVendor,
        protected UpdateVendor $updateVendor,
        protected DeleteVendor $deleteVendor
    ) {
    }

    public function getAllVendor(Request $request): LengthAwarePaginator
    {
        return $this->listVendors->execute($request);
    }

    public function createVendor(array $data): Vendor
    {
        return $this->createVendor->execute($data);
    }

    public function getByIdVendor(int $id): Vendor
    {
        return $this->getVendorById->execute($id);
    }

    public function updateVendor(int $id, array $data): Vendor
    {
        return $this->updateVendor->execute($id, $data);
    }

    public function deleteVendor(int $id): void
    {
        $this->deleteVendor->execute($id);
    }
}
