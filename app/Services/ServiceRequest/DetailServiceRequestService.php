<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequestDetail;
use Illuminate\Http\UploadedFile;
use App\Models\ComplaintImage;
use Illuminate\Support\Facades\Storage;
use App\Services\DeviceService;

class DetailServiceRequestService
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    //function to create detail all service request
    public function createDetailServiceRequest(array $data): ServiceRequestDetail
    {
        // Auto-create/resolve device if device info is provided and device_id is not
        if (!isset($data['device_id']) && isset($data['device_type_id'], $data['brand'], $data['model'], $data['serial_number'])) {
            $device = $this->deviceService->findOrCreateDeviceFromRequest([
                'device_type_id' => $data['device_type_id'],
                'brand'          => $data['brand'],
                'model'          => $data['model'],
                'serial_number'  => $data['serial_number'],
            ]);
            $data['device_id'] = $device->id;
        }

        $serviceRequestDetail = ServiceRequestDetail::create([
            'service_request_id' => $data['service_request_id'],
            'service_type_id' => $data['service_type_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
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
    
    //function to update detail service request
    public function updateDetailServiceRequest(int $id, array $data): ServiceRequestDetail
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        
        // Auto-create/resolve device if device info is provided and device_id is not
        if (!isset($data['device_id']) && isset($data['device_type_id'], $data['brand'], $data['model'], $data['serial_number'])) {
            $device = $this->deviceService->findOrCreateDeviceFromRequest([
                'device_type_id' => $data['device_type_id'],
                'brand'          => $data['brand'],
                'model'          => $data['model'],
                'serial_number'  => $data['serial_number'],
            ]);
            $data['device_id'] = $device->id;
        }

        $updateData = [
            'service_type_id' => $data['service_type_id'] ?? $serviceRequestDetail->service_type_id,
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

    //function to delete detail service request
    public function deleteDetailServiceRequest(int $id): void
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);
        
        // Delete associated images if any
        foreach ($serviceRequestDetail->complaint_images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
        
        $serviceRequestDetail->delete();
    }

    //function to get detail service request by id
    public function getDetailById(int $id): ServiceRequestDetail
    {
        $detail = ServiceRequestDetail::with('device')->findOrFail($id);

        return $detail;
    }
}
