<?php

namespace App\Services\Invoice;

use App\Enums\ServiceRequestStatusCode;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\ServiceCost;
use App\Models\Status;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportInvoiceService
{
    private const MIN_STATUS_FOR_INVOICE = 1;
    
    // Status codes that are NOT allowed to print invoice
    private const BLOCKED_STATUS_CODES = [
        ServiceRequestStatusCode::CANCELLED->value
    ];

    //function to generate invoice by service request id
    public function generateInvoice($serviceRequestId)
    {
        $invoice = \App\Models\Invoice::with([
            'service_request.user.departments', 
            'service_request.operator', 
            'service_request.service_request_details.device.device_model'
        ])->where('service_request_id', $serviceRequestId)->firstOrFail();

        $serviceRequest = $invoice->service_request;
        $completedAt = $this->getCompletedAt($serviceRequest);

        $user = $serviceRequest->user;
        
        if(!isset($user)){
            $user = $serviceRequest->operator;
        }

        $data = [
            'invoice' => $invoice,
            'serviceRequest' => $serviceRequest,
            'user' => $user,
            'operator' => $serviceRequest->operator,
            'device' => $serviceRequest->service_request_details->first()->device ?? null,
            'completedAt' => $completedAt,
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
            'operator',
            'status',
            'service_request_details.device.device_model'
        ])->findOrFail($serviceRequestId);

        // Check if status allows invoice generation
        $statusCode = $serviceRequest->status?->code;
        if (in_array($statusCode, self::BLOCKED_STATUS_CODES)) {
            throw new \Exception('Invoice tidak dapat dicetak. Status request belum disetujui operator.');
        }

        $user = $serviceRequest->user;
        
        if(!isset($user)){
            $user = $serviceRequest->operator;
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
            'user' => $user,
            'operator' => $serviceRequest->operator,
            'device' => $serviceRequest->service_request_details->first()->device ?? null,
            'costs' => $costs,
            'isPreview' => true,
            'completedAt' => $this->getCompletedAt($serviceRequest),
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

    private function getCompletedAt(ServiceRequest $serviceRequest): ?\Carbon\Carbon
    {
        $completedLog = AuditLog::where('entity_type_id', 1)
            ->where('entity_id', $serviceRequest->id)
            ->whereHas('new_status', function ($query) {
                $query->where('code', ServiceRequestStatusCode::COMPLETED->value);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$completedLog?->created_at) {
            return null;
        }

        return $completedLog->created_at instanceof \Carbon\Carbon
            ? $completedLog->created_at
            : \Carbon\Carbon::parse($completedLog->created_at);
    }
}
