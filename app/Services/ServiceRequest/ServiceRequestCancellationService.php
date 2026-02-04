<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceCancellation;
use App\Models\ServiceRequest;
use App\Models\Status;

class ServiceRequestCancellationService
{
    public function createCancellation(array $data, ServiceRequest $serviceRequest): ServiceCancellation
    {
        $cancellation = ServiceCancellation::create([
            'service_request_id' => $serviceRequest->id,
            'reason' => $data['reason'],
            'canceled_by' => $data['canceled_by'] ?? auth()->id(),
            'cancellation_date' => now(),
        ]);

        // Update service request status to cancelled
        $serviceRequest->update(['status_id' => $this->getServiceRequestStatusId('CANCELLED')]);

        return $cancellation->load('cancelledBy');
    }

    public function updateCancellation(int $id, array $data): ServiceCancellation
    {
        $cancellation = ServiceCancellation::findOrFail($id);
        
        $updateData = [
            'reason' => $data['reason'] ?? $cancellation->reason,
            'canceled_by' => $data['canceled_by'] ?? $cancellation->canceled_by,
        ];
        
        $cancellation->update(array_filter($updateData));

        return $cancellation->load('cancelledBy');
    }

    public function deleteCancellation(int $id): void
    {
        $cancellation = ServiceCancellation::findOrFail($id);
        
        // Update service request status back to pending
        $serviceRequest = $cancellation->serviceRequest;
        $serviceRequest->update(['status_id' => $this->getServiceRequestStatusId('PENDING')]);
        
        $cancellation->delete();
    }

    public function getCancellationById(int $id): ServiceCancellation
    {
        $cancellation = ServiceCancellation::with(['serviceRequest', 'cancelledBy'])
            ->findOrFail($id);

        return $cancellation;
    }

    public function getCancellationsByServiceRequest(int $serviceRequestId): \Illuminate\Database\Eloquent\Collection
    {
        $cancellations = ServiceCancellation::with('cancelledBy')
            ->where('service_request_id', $serviceRequestId)
            ->get();

        return $cancellations;
    }

    private function getServiceRequestStatusId(string $code): int
    {
        return Status::idForEntityCode('SERVICE_REQUEST', $code);
    }
}
