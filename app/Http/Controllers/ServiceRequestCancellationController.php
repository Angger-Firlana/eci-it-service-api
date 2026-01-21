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

    public function store(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        
        $data = [
            'reason' => $request->reason,
            'canceled_by' => auth()->id()
        ];

        $cancellation = $this->cancellationService->createCancellation($data, $serviceRequest);

        return APIResponse::success($cancellation, 201, 'Service request cancelled successfully');
    }
}
