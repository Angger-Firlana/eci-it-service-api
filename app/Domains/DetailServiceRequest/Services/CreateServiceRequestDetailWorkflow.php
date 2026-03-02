<?php

namespace App\Domains\DetailServiceRequest\Services;

use App\Domains\DetailServiceRequest\Actions\CheckOrCeateDevice;
use App\Domains\DetailServiceRequest\Actions\InsertComplaintImages;

use App\Domains\DetailServiceRequest\Actions\CreateServiceRequestDetail;


class CreateServiceRequestDetailWorkflow{

    protected CheckOrCeateDevice $checkOrCreateDevice;
    protected InsertComplaintImages $insertComplaintImages;
    protected CreateServiceRequestDetail $createServiceRequestDetail;
    public function __construct(
        CheckOrCeateDevice $checkOrCreateDevice,
        InsertComplaintImages $insertComplaintImages,
        CreateServiceRequestDetail $createServiceRequestDetail
    ) {
        $this->checkOrCreateDevice = $checkOrCreateDevice;
        $this->insertComplaintImages = $insertComplaintImages;
        $this->createServiceRequestDetail = $createServiceRequestDetail;
    }

    public function execute(array $data){
        if (!isset($data['device_id']) && isset($data['device_type_id'], $data['brand'], $data['model'], $data['serial_number'])) {
            $device = $this->checkOrCreateDevice->execute($data);
            $data['device_id'] = $device->id;
        }

        $serviceRequestDetail = $this->createServiceRequestDetail->execute($data);

        // Handle complaint images if any
        if (isset($data['complaint_images']) && is_array($data['complaint_images'])) {
            $this->insertComplaintImages->execute($data['complaint_images'], $serviceRequestDetail);
        }

        return $serviceRequestDetail->load('device');
    }
}