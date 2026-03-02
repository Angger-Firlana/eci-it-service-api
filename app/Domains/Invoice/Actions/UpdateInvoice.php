<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;

class UpdateInvoice
{
    public function execute(int $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);

        if (isset($data['issue_date'])) {
            $invoice->issue_date = $data['issue_date'];
        }

        if (isset($data['due_date'])) {
            $invoice->due_date = $data['due_date'];
        }

        if (isset($data['total_amount'])) {
            $invoice->total_amount = $data['total_amount'];
        }

        if (isset($data['status_id'])) {
            $invoice->status_id = $data['status_id'];
        }

        $invoice->save();

        return $invoice;
    }
}
