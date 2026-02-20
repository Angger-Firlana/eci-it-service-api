<?php

namespace App\Helpers\ServiceRequest;

class ShowRelationsHandler{
     //function to get relations for index
    public function indexWith(): array
    {
        return [
            'user:id,name,email',
            'admin:id,name,email',
            'status:id,name,code',
            'service_request_details:id,service_request_id,device_id,complaint',
            'service_request_details.device:id,device_model_id,serial_number',
            'service_request_details.device.device_model:id,device_type_id,brand,model'
        ];
    }

    //function to get relations for show
    public function showWith(): array
    {
        return [
            'user:id,name,email',
            'user.departments:id,name',
            'admin:id,name,email',
            'admin.departments:id,name',
            'status:id,name,code',
            'service_request_details:id,service_request_id,device_id,complaint,solution',
            'service_request_details.device:id,device_model_id,serial_number,bad_asset',
            'service_request_details.device.device_model:id,device_type_id,brand,model',
            'service_request_details.device.device_model.device_type:id,name',
            'service_request_details.complaint_images:image_path',
            'vendor_approvals:id,service_request_id,approver_id,assigned_by,assigned_at,approved_at,status_id',
            'vendor_approvals.status:id,name,code',
            'vendor_approvals.approver:id,name',
            'vendor_approvals.assigned_by:id,name'
        ];
    }

    //function to get default relations
    public function defaultWith(): array
    {
        return [
            'user:id,name,email',
            'status:id,name,code',
            'service_request_details.device:id,device_model_id,serial_number,bad_asset'
        ];
    }
}