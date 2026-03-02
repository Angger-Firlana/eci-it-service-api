<?php

namespace App\Domains\Invoice\Actions;

use App\Domains\Invoice\Support\CompletedAtResolver;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoice
{
    public function __construct(
        protected CompletedAtResolver $completedAtResolver
    ) {
    }

    public function execute(int $serviceRequestId)
    {
        $invoice = Invoice::with([
            'service_request.user.departments',
            'service_request.operator',
            'service_request.service_request_details.device.device_model',
        ])->where('service_request_id', $serviceRequestId)->firstOrFail();

        $serviceRequest = $invoice->service_request;
        $completedAt = $this->completedAtResolver->resolve($serviceRequest);

        $user = $serviceRequest->user;

        if (!isset($user)) {
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

        return Pdf::loadView('invoice.template', $data);
    }
}
