<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;

class GetInvoicePrintData
{
    public function execute(int $id): array
    {
        $invoice = Invoice::with([
            'service_request.user',
            'service_request.service_request_details' => function ($query) {
                $query->with('device.device_model');
            },
        ])->findOrFail($id);

        $serviceRequest = $invoice->service_request;
        $deviceDetail = $serviceRequest->service_request_details->first();

        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'total_amount' => $invoice->total_amount,
            'status' => $invoice->status?->name ?? 'Unpaid',
            'customer' => [
                'name' => $serviceRequest->user->name,
                'email' => $serviceRequest->user->email,
            ],
            'device' => $deviceDetail ? [
                'brand' => $deviceDetail->device->device_model->brand,
                'model' => $deviceDetail->device->device_model->model,
                'serial_number' => $deviceDetail->device->serial_number,
                'complaint' => $deviceDetail->complaint,
            ] : null,
            'service_request' => [
                'service_number' => $serviceRequest->service_number,
            ],
        ];
    }
}
