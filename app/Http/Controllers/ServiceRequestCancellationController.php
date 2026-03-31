<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Domains\ServiceRequestCancellation\Actions\CreateCancellation;
use App\Domains\ServiceRequestCancellation\Actions\DeleteCancellation;
use App\Domains\ServiceRequestCancellation\Actions\ListCancellationsByServiceRequestId;
use App\Domains\ServiceRequestCancellation\Actions\UpdateCancellation;
use App\Exceptions\ApiException;
use App\Models\ServiceCancellation;
use App\Models\ServiceRequest;
use App\Models\User;

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
        $serviceRequest = ServiceRequest::findOrFail((int) $serviceRequestId);
        $this->authorizeServiceRequestAccess($serviceRequest);

        $cancellation = $this->listCancellationsByServiceRequestId->execute((int) $serviceRequestId);
        return APIResponse::success($cancellation);
    }

    public function update(Request $request, $serviceRequestId, $cancellationId)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $cancellation = ServiceCancellation::findOrFail((int) $cancellationId);
        if ((int) $cancellation->service_request_id !== (int) $serviceRequestId) {
            throw ApiException::badRequest('Cancellation does not belong to this service request');
        }

        $this->authorizeServiceRequestAccess($cancellation->serviceRequest, false);

        $cancellation = $this->updateCancellation->execute((int) $cancellationId, $validated);
        return APIResponse::success($cancellation);
    }

    public function store(Request $request, $serviceRequestId)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $this->authorizeServiceRequestAccess($serviceRequest);
        
        $data = [
            'reason' => $request->reason,
            'cancelled_by' => auth()->id()
        ];

        $cancellation = $this->createCancellation->execute($data, $serviceRequest);

        return APIResponse::success($cancellation, 201, 'Service request cancelled successfully');
    }

    public function destroy($serviceRequestId, $cancellationId)
    {
        $cancellation = ServiceCancellation::findOrFail((int) $cancellationId);
        if ((int) $cancellation->service_request_id !== (int) $serviceRequestId) {
            throw ApiException::badRequest('Cancellation does not belong to this service request');
        }

        $this->authorizeServiceRequestAccess($cancellation->serviceRequest, false);
        $this->deleteCancellation->execute((int) $cancellationId);
        return APIResponse::success(null, 204, 'Service request cancellation deleted successfully');
    }

    private function authorizeServiceRequestAccess(ServiceRequest $serviceRequest, bool $allowOwner = true): void
    {
        $user = User::with('roles')->findOrFail(auth('sanctum')->id());
        $roles = $user->roles->pluck('name');

        if ($roles->intersect(['admin', 'operator', 'manager'])->isNotEmpty()) {
            return;
        }

        if ($allowOwner && $roles->contains('user') && (int) $serviceRequest->user_id === (int) $user->id) {
            return;
        }

        throw ApiException::forbidden('You are not allowed to manage this cancellation');
    }
}
