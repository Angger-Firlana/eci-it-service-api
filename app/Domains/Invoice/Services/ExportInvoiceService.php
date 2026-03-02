<?php

namespace App\Domains\Invoice\Services;

use App\Domains\Invoice\Actions\CanPrintInvoice;
use App\Domains\Invoice\Actions\GenerateInvoice;
use App\Domains\Invoice\Actions\GeneratePreviewInvoice;

class ExportInvoiceService
{
    public function __construct(
        protected GenerateInvoice $generateInvoice,
        protected GeneratePreviewInvoice $generatePreviewInvoice,
        protected CanPrintInvoice $canPrintInvoice
    ) {
    }

    public function generateInvoice(int $serviceRequestId)
    {
        return $this->generateInvoice->execute($serviceRequestId);
    }

    public function generatePreviewInvoice(int $serviceRequestId)
    {
        return $this->generatePreviewInvoice->execute($serviceRequestId);
    }

    public function canPrintInvoice(int $serviceRequestId): bool
    {
        return $this->canPrintInvoice->execute($serviceRequestId);
    }
}
