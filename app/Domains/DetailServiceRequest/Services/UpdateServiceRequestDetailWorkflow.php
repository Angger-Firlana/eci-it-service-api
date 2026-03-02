<?php

namespace App\Domains\DetailServiceRequest\Services;

use App\Domains\DetailServiceRequest\Actions\CheckOrCeateDevice;
use App\Domains\DetailServiceRequest\Actions\InsertComplaintImages;

use App\Domains\DetailServiceRequest\Actions\UpdateServiceRequestDetail;


class UpdateServiceRequestDetailWorkflow{

    protected CheckOrCeateDevice $checkOrCreateDevice;
    protected InsertComplaintImages $insertComplaintImages;
    protected UpdateServiceRequestDetail $updateServiceRequestDetail;
    public function __construct(
        CheckOrCeateDevice $checkOrCreateDevice,
        InsertComplaintImages $insertComplaintImages,
        UpdateServiceRequestDetail $updateServiceRequestDetail
    ) {
        $this->checkOrCreateDevice = $checkOrCreateDevice;
        $this->insertComplaintImages = $insertComplaintImages;
        $this->updateServiceRequestDetail = $updateServiceRequestDetail;
    }

    public function execute(int $id, array $data){
        if (!isset($data['device_id']) && isset($data['device_type_id'], $data['brand'], $data['model'], $data['serial_number'])) {
            $device = $this->checkOrCreateDevice->execute($data);
            $data['device_id'] = $device->id;
        }

        $serviceRequestDetail = $this->updateServiceRequestDetail->execute($id, $data);

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            $this->insertComplaintImages->execute($data['complaint_images'], $serviceRequestDetail);
        }

        return $serviceRequestDetail->load('device');
    }
}