<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportInvoiceService
{
    public function generateInvoice($serviceRequestId)
    {
        $invoice = \App\Models\Invoice::with([
            'service_request.user', 
            'service_request.admin', 
            'service_request.service_request_details.device.device_model'
        ])->where('service_request_id', $serviceRequestId)->firstOrFail();

        $serviceRequest = $invoice->service_request;
        
        $data = [
            'invoice' => $invoice,
            'serviceRequest' => $serviceRequest,
            'user' => $serviceRequest->user,
            'admin' => $serviceRequest->admin,
            'device' => $serviceRequest->service_request_details->first()->device ?? null,
        ];

        $pdf = PDF::loadView('invoice.template', $data);

        return $pdf;
    }
}
