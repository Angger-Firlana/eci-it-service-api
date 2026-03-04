<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestCancellation\Actions\CreateCancellation;
use App\Domains\ServiceRequestCancellation\Actions\DeleteCancellation;
use App\Domains\ServiceRequestCancellation\Actions\ListCancellationsByServiceRequestId;
use App\Domains\ServiceRequestCancellation\Actions\UpdateCancellation;
use App\Models\ServiceRequest;

class ServiceRequestCancellationController extends Controller
{
    protected CreateCancellation $createCancellation;
    protected UpdateCancellation $updateCancellation;
    protected DeleteCancellation $deleteCancellation;
    protected ListCancellationsByServiceRequestId $listCancellationsByServiceRequestId;

    public function __construct(
        CreateCancellation $createCancellation,
        UpdateCancellation $updateCancellation,
        DeleteCancellation $deleteCancellation,
        ListCancellationsByServiceRequestId $listCancellationsByServiceRequestId
    )
    {
        $this->createCancellation = $createCancellation;
        $this->updateCancellation = $updateCancellation;
        $this->deleteCancellation = $deleteCancellation;
        $this->listCancellationsByServiceRequestId = $listCancellationsByServiceRequestId;
    }

    public function index($serviceRequestId)
    {
        $cancellation = $this->listCancellationsByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($cancellation);
    }

    public function update(Request $request, $cancellationId)
    {
        $cancellation = $this->updateCancellation->execute((int) $cancellationId, $request->all());
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

        $cancellation = $this->createCancellation->execute($data, $serviceRequest);

        return APIResponse::success($cancellation, 201, 'Service request cancelled successfully');
    }

    public function destroy($cancellationId)
    {
        $this->deleteCancellation->execute((int) $cancellationId);
        return APIResponse::success(null, 204, 'Service request cancellation deleted successfully');
    }
}
