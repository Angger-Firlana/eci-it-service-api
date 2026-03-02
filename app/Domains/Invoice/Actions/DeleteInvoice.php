<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;

class DeleteInvoice
{
    public function execute(int $id): void
    {
        Invoice::findOrFail($id)->delete();
    }
}
