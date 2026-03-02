<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;

class GetInvoiceById
{
    public function execute(int $id): Invoice
    {
        return Invoice::findOrFail($id);
    }
}
