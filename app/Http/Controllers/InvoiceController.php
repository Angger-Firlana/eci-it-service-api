<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\Invoice\Services\InvoiceService;
use App\Domains\Invoice\Services\ExportInvoiceService;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected ExportInvoiceService $exportInvoiceService;

    public function __construct(
        InvoiceService $invoiceService,
        ExportInvoiceService $exportInvoiceService
    ){
        $this->invoiceService = $invoiceService;
        $this->exportInvoiceService = $exportInvoiceService;
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

    public function download($id)
    {
        $invoice = $this->invoiceService->getInvoiceById((int) $id);
        $pdf = $this->exportInvoiceService->generateInvoice((int) $invoice->service_request_id);

        return $pdf->download('invoice-' . $invoice->id . '.pdf');
    }
}
