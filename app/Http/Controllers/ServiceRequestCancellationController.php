<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestCancellation\Services\ServiceRequestCancellationService;
use App\Models\ServiceRequest;

class ServiceRequestCancellationController extends Controller
{
    protected $cancellationService;

    public function __construct(ServiceRequestCancellationService $cancellationService)
    {
        $this->cancellationService = $cancellationService;
    }

    public function index($serviceRequestId)
    {
        $cancellation = $this->cancellationService->getCancellationByServiceRequest($serviceRequestId);
        return APIResponse::success($cancellation);
    }

    public function update(Request $request, $cancellationId)
    {
        $cancellation = $this->cancellationService->updateCancellation($request->all(), $cancellationId);
        return APIResponse::success($cancellation);
    }

    public function store(Request $request, $serviceRequestId)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        
        $data = [
            'reason' => $request->reason,
            'canceled_by' => auth()->id()
        ];

        $cancellation = $this->cancellationService->createCancellation($data, $serviceRequest);

        return APIResponse::success($cancellation, 201, 'Service request cancelled successfully');
    }

    public function destroy($cancellationId)
    {
        $this->cancellationService->deleteCancellation($cancellationId);
        return APIResponse::success(null, 204, 'Service request cancellation deleted successfully');
    }
}
