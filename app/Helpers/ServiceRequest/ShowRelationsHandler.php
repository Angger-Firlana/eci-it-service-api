<?php

namespace App\Helpers\ServiceRequest;

class ShowRelationsHandler{
     //function to get relations for index
    private function indexWith(): array
    {
        return [
            'user',
            'admin',
            'status'
        ];
    }

    //function to get relations for show
    private function showWith(): array
    {
        return [
            'user:id,name,email',
            'user.departments:id,name',
            'admin:id,name,email',
            'admin.departments:id,name',
            'status:id,name,code',
            'service_request_details:id,service_request_id,service_type_id,device_id,complaint',
            'service_request_details.device:id,device_model_id,serial_number,bad_asset',
            'service_request_details.device.device_model:id,device_type_id,brand,model',
            'service_request_details.device.device_model.device_type:id,name',
            'service_request_details.service_type:id,name',
            'service_request_details.complaint_images',
            'vendor_approvals:id,service_request_id,approver_id,assigned_by,assigned_at,approved_at,status_id,created_at,updated_at',
            'vendor_approvals.status:id,name,code',
            'vendor_approvals.approver:id,name',
            'vendor_approvals.assigned_by:id,name'
        ];


    }

    //function to get default relations
    private function defaultWith(): array
    {
        return [
            'user',
            'status',
            'service_request_details.device',
        ];
    }
}