<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    //
    public function getAllInvoice(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
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

        return $invoices;   
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

    public function updateInvoice($id, array $data): Invoice{
        $invoice = Invoice::findOrFail($id);
        if(isset($data['issue_date'])){
            $invoice->issue_date = $data['issue_date'];
        }

        if(isset($data['due_date'])){
            $invoice->due_date = $data['due_date'];
        }

        if(isset($data['total_amount'])){
            $invoice->total_amount = $data['total_amount'];
        }

        if(isset($data['status_id'])){
            $invoice->status_id = $data['status_id'];
        }

        $invoice->save();

        return $invoice;
    }

    public function deleteInvoice($id):void{
        Invoice::findOrFail($id)->delete();
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = "INV";
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::whereDate('created_at', today())->orderBy('created_at', 'desc')->first();
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    } 
    
    public function getInvoicePrintData($id)
    {
        $invoice = Invoice::with([
            'service_request.user',
            'service_request.service_request_details' => function($query) {
                // We only need one device for the print view, but we can't limit eager load easily in a way that just picks one per parent if we have multiple. 
                // However, we can just take the first one in PHP.
                // Let's load the device info.
                $query->with('device.device_model');
            }
        ])->findOrFail($id);

        $serviceRequest = $invoice->service_request;
        $deviceDetail = $serviceRequest->service_request_details->first();
        
        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'total_amount' => $invoice->total_amount,
            'status' => $invoice->status?->name ?? 'Unpaid',
            'customer' => [
                'name' => $serviceRequest->user->name,
                'email' => $serviceRequest->user->email,
                // Add address or phone if available in User model
            ],
            'device' => $deviceDetail ? [
                'brand' => $deviceDetail->device->device_model->brand,
                'model' => $deviceDetail->device->device_model->model,
                'serial_number' => $deviceDetail->device->serial_number,
                'complaint' => $deviceDetail->complaint
            ] : null,
            'service_request' => [
                'service_number' => $serviceRequest->service_number,
            ]
        ];
    }

    public function createInvoiceForServiceRequest(ServiceRequest $serviceRequest, array $data): void
    {
        $adminId = $data['admin_id'] ?? $serviceRequest->admin_id;
        if (!$adminId) {
            throw new \Exception('Admin wajib diisi untuk mengubah status menjadi selesai/invoice.');
        }

        $totalAmount = $serviceRequest->service_costs()->sum('amount');

        $this->createInvoice([
            'service_request_id' => $serviceRequest->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => $totalAmount,
            'status_id' => $this->getInvoiceStatusId('SENT'),
        ]);
    }

    private function getInvoiceStatusId(string $code): int
    {
        return Status::idForEntityCode('INVOICE', $code);
    }
}
