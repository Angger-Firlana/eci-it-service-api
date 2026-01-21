<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Helpers\APIResponse;
use App\Services\Invoice\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService){
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request){
        $invoices = $this->invoiceService->getAllInvoice($request);
        return APIResponse::success($invoices);
    }

    public function show($id){
        $invoice = $this->invoiceService->getInvoiceById($id);
        return APIResponse::success($invoice);
    }

    public function print($id){
        $data = $this->invoiceService->getInvoicePrintData($id);
        return APIResponse::success($data);
    }
}
