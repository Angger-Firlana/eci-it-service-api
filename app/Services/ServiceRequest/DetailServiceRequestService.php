<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequestDetail;
use Illuminate\Http\UploadedFile;
use App\Models\ComplaintImage;
use Illuminate\Support\Facades\Storage;

class DetailServiceRequestService
{
    public function createDetailServiceRequest(array $data): ServiceRequestDetail
    {
        $serviceRequestDetail = ServiceRequestDetail::create([
            'service_request_id' => $data['service_request_id'],
            'device_id' => $data['device_id'],
            'complaint' => $data['complaint'],
        ]);

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            foreach ($data['complaint_images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $imagePath = $image->store('complaint-images', 'public');
                    ComplaintImage::create([
                        'service_request_detail_id' => $serviceRequestDetail->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
            
        }

        return $serviceRequestDetail->load('device');
    }
    
    public function updateDetailServiceRequest(int $id, array $data): ServiceRequestDetail
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        
        $updateData = [
            'device_id' => $data['device_id'] ?? $serviceRequestDetail->device_id,
            'complaint' => $data['complaint'] ?? $serviceRequestDetail->complaint,
        ];
        
        $serviceRequestDetail->update(array_filter($updateData));

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            foreach ($data['complaint_images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $imagePath = $image->store('complaint-images', 'public');
                    ComplaintImage::updateOrCreate([
                        'service_request_detail_id' => $serviceRequestDetail->id,
                        'image_path' => $imagePath,
                    ]); 
                }
            }
            
        }

        return $serviceRequestDetail->load('device');
    }

    public function deleteDetailServiceRequest(int $id): void
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        
        // Delete associated images if any
        if ($serviceRequestDetail->complaint_images) {
            $images = json_decode($serviceRequestDetail->complaint_images, true) ?? [];
            foreach ($images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }
        
        $serviceRequestDetail->delete();
    }

    public function getDetailById(int $id): ServiceRequestDetail
    {
        $detail = ServiceRequestDetail::with('device')->findOrFail($id);

        return $detail;
    }
}
