<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportInvoiceService;

class ExportInvoiceController extends Controller
{
    protected $exportInvoiceService;

    public function __construct(ExportInvoiceService $exportInvoiceService)
    {
        $this->exportInvoiceService = $exportInvoiceService;
    }

    public function download($serviceRequestId)
    {
        $pdf = $this->exportInvoiceService->generateInvoice($serviceRequestId);

        return $pdf->download('invoice-' . $serviceRequestId . '.pdf');
    }
}