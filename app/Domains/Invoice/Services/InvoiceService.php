<?php

namespace App\Domains\Invoice\Services;

use App\Domains\Invoice\Actions\CreateInvoice;
use App\Domains\Invoice\Actions\CreateInvoiceForServiceRequest;
use App\Domains\Invoice\Actions\DeleteInvoice;
use App\Domains\Invoice\Actions\GetInvoiceById;
use App\Domains\Invoice\Actions\GetInvoicePrintData;
use App\Domains\Invoice\Actions\ListInvoices;
use App\Domains\Invoice\Actions\UpdateInvoice;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class InvoiceService
{
    public function __construct(
        protected ListInvoices $listInvoices,
        protected GetInvoiceById $getInvoiceById,
        protected CreateInvoice $createInvoice,
        protected UpdateInvoice $updateInvoice,
        protected DeleteInvoice $deleteInvoice,
        protected GetInvoicePrintData $getInvoicePrintData,
        protected CreateInvoiceForServiceRequest $createInvoiceForServiceRequest
    ) {
    }

    public function getAllInvoice(Request $request): LengthAwarePaginator
    {
        return $this->listInvoices->execute($request);
    }

    public function getInvoiceById(int $id): Invoice
    {
        return $this->getInvoiceById->execute($id);
    }

    public function createInvoice(array $data): Invoice
    {
        return $this->createInvoice->execute($data);
    }

    public function updateInvoice(int $id, array $data): Invoice
    {
        return $this->updateInvoice->execute($id, $data);
    }

    public function deleteInvoice(int $id): void
    {
        $this->deleteInvoice->execute($id);
    }

    public function getInvoicePrintData(int $id): array
    {
        return $this->getInvoicePrintData->execute($id);
    }

    public function createInvoiceForServiceRequest(ServiceRequest $serviceRequest): void
    {
        $this->createInvoiceForServiceRequest->execute($serviceRequest);
    }
}
