<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VendorService;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;

class VendorController extends Controller
{
    //
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    public function index(Request $request){
        $vendors = $this->vendorService->getAllVendor($request);
        return response()->json($vendors);
    }

    public function store(StoreVendorRequest $request){
        $vendor = $this->vendorService->createVendor($request->validated());
        return response()->json($vendor, 201);
    }

    public function show($id){
        $vendor = $this->vendorService->getByIdVendor($id);
        return response()->json($vendor);
    }

    public function update($id, UpdateVendorRequest $request){
        $vendor = $this->vendorService->updateVendor($id, $request->validated());
        return response()->json($vendor);
    }

    public function destroy($id){
        $this->vendorService->deleteVendor($id);
        return response()->json(null, 204);
    }
}
