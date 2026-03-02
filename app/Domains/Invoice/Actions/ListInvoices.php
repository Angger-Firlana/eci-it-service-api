<?php

namespace App\Domains\Invoice\Actions;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListInvoices
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('serviceRequest')
            ->when($request->has('service_request_id'), function ($query) use ($request) {
                $query->where('service_request_id', $request->service_request_id);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('vendor_id'), function ($query) use ($request) {
                $query->where('vendor_id', $request->vendor_id);
            })
            ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);
    }
}
