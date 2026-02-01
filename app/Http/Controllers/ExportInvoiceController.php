<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportInvoiceService;
use App\Helpers\APIResponse;

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

    /**
     * Download preview invoice (available when status >= APPROVED_BY_ADMIN)
     */
    public function downloadPreview($serviceRequestId)
    {
        try {
            $pdf = $this->exportInvoiceService->generatePreviewInvoice($serviceRequestId);
            return $pdf->download('invoice-preview-' . $serviceRequestId . '.pdf');
        } catch (\Exception $e) {
            return APIResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Check if invoice can be printed for a service request
     */
    public function canPrint($serviceRequestId)
    {
        $canPrint = $this->exportInvoiceService->canPrintInvoice($serviceRequestId);
        return APIResponse::success(['can_print' => $canPrint]);
    }
}