<?php
namespace App\Domains\ServiceRequest\Actions;

use App\Models\ComplaintImage;
use App\Models\Invoice;
use App\Models\ServiceCancellation;
use App\Models\ServiceCost;
use App\Models\ServiceLocation;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDetail;
use App\Models\VendorApproval;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

class DeleteServiceRequest{
    public function execute(int $id): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->loadMissing('status');
        if ($serviceRequest->status?->code === ServiceRequestStatusCode::COMPLETED->value) {
            throw ApiException::conflict('Cannot delete completed service request');
        }

        // Related rows are tied to the request via foreign keys without ON DELETE
        // CASCADE, so they must be removed first (in a transaction) to avoid
        // constraint violations. Audit logs are intentionally kept (no FK; they
        // reference entity_id polymorphically and remain as history).
        DB::transaction(function () use ($serviceRequest) {
            $detailIds = ServiceRequestDetail::where('service_request_id', $serviceRequest->id)->pluck('id');
            if ($detailIds->isNotEmpty()) {
                ComplaintImage::whereIn('service_request_detail_id', $detailIds)->delete();
            }
            ServiceRequestDetail::where('service_request_id', $serviceRequest->id)->delete();
            ServiceLocation::where('service_request_id', $serviceRequest->id)->delete();
            ServiceCost::where('service_request_id', $serviceRequest->id)->delete();
            ServiceCancellation::where('service_request_id', $serviceRequest->id)->delete();
            VendorApproval::where('service_request_id', $serviceRequest->id)->delete();
            Invoice::where('service_request_id', $serviceRequest->id)->delete();

            $serviceRequest->delete();
        });

        return $serviceRequest;
    }
}
