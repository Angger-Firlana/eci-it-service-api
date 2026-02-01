<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ServiceCost;
use App\Models\Status;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportInvoiceService
{
    // Minimum status_id required to print invoice (APPROVED_BY_ADMIN = 3)
    private const MIN_STATUS_FOR_INVOICE = 3;
    
    // Status codes that are NOT allowed to print invoice
    private const BLOCKED_STATUS_CODES = ['PENDING', 'IN_REVIEW_ADMIN', 'REJECTED', 'CANCELLED'];

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

    /**
     * Generate invoice PDF on-the-fly without requiring stored Invoice record.
     * Available when status >= APPROVED_BY_ADMIN.
     */
    public function generatePreviewInvoice($serviceRequestId)
    {
        $serviceRequest = ServiceRequest::with([
            'user',
            'admin',
            'status',
            'service_request_details.device.device_model',
            'service_request_details.service_type',
        ])->findOrFail($serviceRequestId);

        // Check if status allows invoice generation
        $statusCode = $serviceRequest->status?->code;
        if (in_array($statusCode, self::BLOCKED_STATUS_CODES)) {
            throw new \Exception('Invoice tidak dapat dicetak. Status request belum disetujui admin.');
        }

        // Get costs for this service request
        $costs = ServiceCost::where('service_request_id', $serviceRequestId)->get();
        $totalAmount = $costs->sum('amount');

        // Build virtual invoice object for the template
        $invoice = new \stdClass();
        $invoice->invoice_number = 'PRV-' . $serviceRequest->service_number;
        $invoice->issue_date = now();
        $invoice->due_date = now()->addDays(30);
        $invoice->total_amount = $totalAmount;

        $data = [
            'invoice' => $invoice,
            'serviceRequest' => $serviceRequest,
            'user' => $serviceRequest->user,
            'admin' => $serviceRequest->admin,
            'device' => $serviceRequest->service_request_details->first()->device ?? null,
            'costs' => $costs,
            'isPreview' => true,
        ];

        $pdf = PDF::loadView('invoice.preview-template', $data);

        return $pdf;
    }

    /**
     * Check if a service request can have its invoice printed.
     */
    public function canPrintInvoice($serviceRequestId): bool
    {
        $serviceRequest = ServiceRequest::with('status')->find($serviceRequestId);
        if (!$serviceRequest) {
            return false;
        }

        $statusCode = $serviceRequest->status?->code;
        return !in_array($statusCode, self::BLOCKED_STATUS_CODES);
    }
}
