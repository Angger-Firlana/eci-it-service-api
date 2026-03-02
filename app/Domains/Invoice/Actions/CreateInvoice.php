<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;

class CreateInvoice
{
    public function execute(array $data): Invoice
    {
        return Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'service_request_id' => $data['service_request_id'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'total_amount' => $data['total_amount'],
            'status_id' => $data['status_id'],
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::whereDate('created_at', today())->orderBy('created_at', 'desc')->first();
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
