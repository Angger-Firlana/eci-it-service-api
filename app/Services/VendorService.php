<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorService
{
    //
    public function getAllVendor(Request $request)
    {
        $vendors = Vendor::filter($request)->paginate($request->get('per_page', 15));
        
        return $vendors;
    }

    public function createVendor(array $data):Vendor{
        $vendor = Vendor::create(array_filter($data));

        return $vendor;
    }

    public function getByIdVendor($id):Vendor{
        $vendor = Vendor::findOrFail($id);

        return $vendor;
    }

    public function updateVendor($id, array $data):Vendor{
        $vendor = Vendor::findOrFail($id);

        if(isset($data['name'])){
            $vendor->name = $data['name'];
        }

        if(isset($data['maps_url'])){
            $vendor->maps_url = $data['maps_url'];
        }

        if(isset($data['description'])){
            $vendor->description = $data['description'];
        }

        $vendor->save();

        return $vendor;
    }

    public function deleteVendor($id):void{
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
    }
}