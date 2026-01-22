<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Helpers\APIResponse;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService){
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request){
        $paginator = $this->invoiceService->getAllInvoice($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "Success", $meta);
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
