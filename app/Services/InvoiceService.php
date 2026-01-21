<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    //
    public function getAllInvoice(Request $request):Collection
    {
        $invoices = Invoice::query()
            ->with('serviceRequest')
            ->when($request->has('service_request_id'), function($query) use ($request){
                $query->where('service_request_id', $request->service_request_id);
            })
            ->when($request->has('status'), function($query) use ($request){
                $query->where('status', $request->status);
            })
            ->when($request->has('vendor_id'), function($query) use ($request){
                $query->where('vendor_id', $request->vendor_id);
            })
            ->when($request->has('start_date') && $request->has('end_date'), function($query) use ($request){
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->when($request->has('search'), function($query) use ($request){
                $query->where('invoice_number', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return $invoices->getCollection();   
    }

    public function getInvoiceById($id):Invoice
    {
        return Invoice::findOrFail($id);
    }

    public function createInvoice(array $data): Invoice{
        return Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'service_request_id' => $data['service_request_id'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'total_amount' => $data['total_amount'],
            'status_id' => $data['status_id']
        ]);
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = "INV";
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::whereDate('created_at', today())->orderBy('created_at', 'desc')-first();
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }   
}