<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Services\ServiceRequest\ServiceRequestCancellationService;
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
        $cancellation = $this->cancellationService->getCancellationByServiceRequestId($serviceRequestId);
        return APIResponse::success($cancellation);
    }

    public function update(Request $request, $serviceRequestId)
    {
        $cancellation = $this->cancellationService->updateCancellation($request->all(), $serviceRequestId);
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
}
