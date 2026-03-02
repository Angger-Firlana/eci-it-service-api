<?php

namespace App\Domains\ServiceRequestDetail\Actions;

use App\Models\ServiceRequestDetail;

class DeleteServiceRequestDetail{
    public function deleteDetailServiceRequest(int $id): void
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        
        // Delete associated images if any
        foreach ($serviceRequestDetail->complaint_images as $image) {
            $imagePath = str_replace('images/', '', $image->image_path);
            if (file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
            $image->delete();
        }
        
        $serviceRequestDetail->delete();
    }
}
