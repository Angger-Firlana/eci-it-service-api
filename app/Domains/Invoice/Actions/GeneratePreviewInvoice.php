<?php

namespace App\Domains\Invoice\Actions;

use App\Domains\Invoice\Support\CompletedAtResolver;
use App\Domains\Invoice\Support\InvoicePrintRules;
use App\Exceptions\ApiException;
use App\Models\ServiceCost;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class GeneratePreviewInvoice
{
    public function __construct(
        protected CompletedAtResolver $completedAtResolver
    ) {
    }

    public function execute(int $serviceRequestId)
    {
        $serviceRequest = ServiceRequest::with([
            'user.departments',
            'operator.departments',
            'status',
            'service_request_details.device.device_model',
        ])->findOrFail($serviceRequestId);

        $statusCode = $serviceRequest->status?->code;
        if (InvoicePrintRules::isBlocked($statusCode)) {
            throw ApiException::badRequest('Invoice tidak dapat dicetak. Status request belum disetujui operator.');
        }

        $user = $serviceRequest->user;

        if (!isset($user)) {
            $user = $serviceRequest->operator;
        }

        $costs = ServiceCost::where('service_request_id', $serviceRequestId)->get();
        $totalAmount = $costs->sum('amount');

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
            'completedAt' => $this->completedAtResolver->resolve($serviceRequest),
        ];

        return Pdf::loadView('invoice.preview-template', $data);
    }
}
