<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Vendor\Actions\CreateVendor;
use App\Domains\Vendor\Actions\DeleteVendor;
use App\Domains\Vendor\Actions\GetVendorById;
use App\Domains\Vendor\Actions\ListVendors;
use App\Domains\Vendor\Actions\UpdateVendor;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;

class VendorController extends Controller
{
    protected ListVendors $listVendors;
    protected CreateVendor $createVendor;
    protected GetVendorById $getVendorById;
    protected UpdateVendor $updateVendor;
    protected DeleteVendor $deleteVendor;

    public function __construct(
        ListVendors $listVendors,
        CreateVendor $createVendor,
        GetVendorById $getVendorById,
        UpdateVendor $updateVendor,
        DeleteVendor $deleteVendor
    )
    {
        $this->listVendors = $listVendors;
        $this->createVendor = $createVendor;
        $this->getVendorById = $getVendorById;
        $this->updateVendor = $updateVendor;
        $this->deleteVendor = $deleteVendor;
    }

    public function index(Request $request){
        $vendors = $this->listVendors->execute($request);
        return response()->json($vendors);
    }

    public function store(StoreVendorRequest $request){
        $vendor = $this->createVendor->execute($request->validated());
        return response()->json($vendor, 201);
    }

    public function show($id){
        $vendor = $this->getVendorById->execute((int) $id);
        return response()->json($vendor);
    }

    public function update($id, UpdateVendorRequest $request){
        $vendor = $this->updateVendor->execute((int) $id, $request->validated());
        return response()->json($vendor);
    }

    public function destroy($id){
        $this->deleteVendor->execute((int) $id);
        return response()->json(null, 204);
    }
}
