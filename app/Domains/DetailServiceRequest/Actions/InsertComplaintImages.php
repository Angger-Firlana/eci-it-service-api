<?php

namespace App\Domains\DetailServiceRequest\Actions;

use Illuminate\Http\UploadedFile;
use App\Models\ComplaintImage;
use Illuminate\Support\Str;

class InsertComplaintImages{
    public function execute(array $complaintImages, $serviceRequestDetail):void{
        foreach ($complaintImages as $image) {
            if ($image instanceof UploadedFile) {

                $fileImageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->move(public_path('images/'), $fileImageName);
                ComplaintImage::create([
                    'service_request_detail_id' => $serviceRequestDetail->id,
                    'image_path' => "images/{$fileImageName}",
                ]);
            }
        }
    }
}