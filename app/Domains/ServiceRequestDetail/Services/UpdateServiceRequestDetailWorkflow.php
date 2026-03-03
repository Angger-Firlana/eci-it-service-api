<?php

namespace App\Domains\ServiceRequestDetail\Services;

use App\Domains\ServiceRequestDetail\Actions\CheckOrCreateDevice;
use App\Domains\ServiceRequestDetail\Actions\InsertComplaintImages;
use App\Domains\ServiceRequestDetail\Actions\UpdateServiceRequestDetail;
use App\Models\ServiceRequestDetail;


class UpdateServiceRequestDetailWorkflow
{
    protected CheckOrCreateDevice $checkOrCreateDevice;
    protected InsertComplaintImages $insertComplaintImages;
    protected UpdateServiceRequestDetail $updateServiceRequestDetail;
    public function __construct(
        CheckOrCreateDevice $checkOrCreateDevice,
        InsertComplaintImages $insertComplaintImages,
        UpdateServiceRequestDetail $updateServiceRequestDetail
    ) {
        $this->checkOrCreateDevice = $checkOrCreateDevice;
        $this->insertComplaintImages = $insertComplaintImages;
        $this->updateServiceRequestDetail = $updateServiceRequestDetail;
    }

    public function execute(int $id, array $data)
    {
        $serviceRequestDetail = ServiceRequestDetail::findOrFail($id);

        if (!isset($data['device_id']) && isset($data['brand'], $data['model'], $data['serial_number'])) {
            $data['device_type_id'] = $serviceRequestDetail->device_type_id;
            $device = $this->checkOrCreateDevice->execute($data);
            $data['device_id'] = $device->id;
        }

        $serviceRequestDetail = $this->updateServiceRequestDetail->execute($serviceRequestDetail, $data);

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            $this->insertComplaintImages->execute($data['complaint_images'], $serviceRequestDetail);
        }

        return $serviceRequestDetail->load('device');
    }
}
