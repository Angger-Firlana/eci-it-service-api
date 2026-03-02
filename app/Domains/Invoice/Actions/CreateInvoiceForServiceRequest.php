<?php

namespace App\Domains\Invoice\Actions;

use App\Enums\InvoiceStatusCode;
use App\Models\ServiceRequest;
use App\Models\Status;

class CreateInvoiceForServiceRequest
{
    public function __construct(
        protected CreateInvoice $createInvoice
    ) {
    }

    public function execute(ServiceRequest $serviceRequest): void
    {
        $totalAmount = $serviceRequest->service_costs()->sum('amount');

        $this->createInvoice->execute([
            'service_request_id' => $serviceRequest->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => $totalAmount,
            'status_id' => $this->getInvoiceStatusId(InvoiceStatusCode::SENT),
        ]);
    }

    private function getInvoiceStatusId(InvoiceStatusCode|string $code): int
    {
        return Status::idForEntityCode('INVOICE', $code);
    }
}
