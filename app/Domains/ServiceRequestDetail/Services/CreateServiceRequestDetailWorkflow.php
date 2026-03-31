<?php

namespace App\Domains\ServiceRequestDetail\Services;

use App\Domains\ServiceRequestDetail\Actions\CheckOrCreateDevice;
use App\Domains\ServiceRequestDetail\Actions\InsertComplaintImages;
use App\Domains\ServiceRequestDetail\Actions\CreateServiceRequestDetail;
use App\Models\Device;


class CreateServiceRequestDetailWorkflow
{
    protected CheckOrCreateDevice $checkOrCreateDevice;
    protected InsertComplaintImages $insertComplaintImages;
    protected CreateServiceRequestDetail $createServiceRequestDetail;
    public function __construct(
        CheckOrCreateDevice $checkOrCreateDevice,
        InsertComplaintImages $insertComplaintImages,
        CreateServiceRequestDetail $createServiceRequestDetail
    ) {
        $this->checkOrCreateDevice = $checkOrCreateDevice;
        $this->insertComplaintImages = $insertComplaintImages;
        $this->createServiceRequestDetail = $createServiceRequestDetail;
    }

    public function execute(array $data)
    {
        if (!isset($data['device_id']) && isset($data['device_type_id'], $data['brand'], $data['model'], $data['serial_number'])) {
            $device = $this->checkOrCreateDevice->execute($data);
            $data['device_id'] = $device->id;
        }

        if (isset($data['device_id']) && !isset($data['device_type_id'])) {
            $device = Device::with('device_model')->findOrFail($data['device_id']);
            $data['device_type_id'] = $device->device_model->device_type_id;
        }

        $serviceRequestDetail = $this->createServiceRequestDetail->execute($data);

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            $this->insertComplaintImages->execute($data['complaint_images'], $serviceRequestDetail);
        }

        return $serviceRequestDetail->load('device');
    }
}
