<?php

namespace App\Domains\Notification\Actions;

use App\Domains\AuditLog\Services\AuditLogService;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Mail\StatusChangeNotification;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Mail;

class QueueStatusChangeNotificationEmail
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {
    }

    public function execute(ServiceRequest $serviceRequest, string $statusCode, ?string $notes = null): void
    {
        if (!in_array($statusCode, [
            ServiceRequestStatusCode::COMPLETED->value,
            ServiceRequestStatusCode::REJECTED_BY_ABOVE->value,
        ], true)) {
            return;
        }

        $user = $serviceRequest->user;
        if (!$user || !$user->email) {
            return;
        }

        $statusLabel = match ($statusCode) {
            ServiceRequestStatusCode::COMPLETED->value => 'Selesai',
            ServiceRequestStatusCode::REJECTED_BY_ABOVE->value => 'Ditolak',
            default => 'Diperbarui',
        };

        $serviceRequest->load([
            'service_request_details.device.device_model.device_type',
        ]);

        $detail = $serviceRequest->service_request_details->first();
        $deviceName = $detail?->device_type?->name;
        $deviceModel = null;
        if ($detail?->device?->device_model) {
            $deviceModel = trim(
                ($detail->device->device_model->brand ?? '') . ' ' . ($detail->device->device_model->model ?? '')
            ) ?: null;
        }

        $frontendUrl = rtrim((string) config('app.frontend_url', ''), '/');
        $serviceRequestUrl = $frontendUrl ? $frontendUrl . '/service-requests/' . $serviceRequest->id : null;

        try {
            Mail::to($user->email)->queue(new StatusChangeNotification(
                userName: $user->name,
                userEmail: $user->email,
                serviceNumber: $serviceRequest->service_number,
                serviceRequestId: $serviceRequest->id,
                statusLabel: $statusLabel,
                statusCode: $statusCode,
                serviceRequestUrl: $serviceRequestUrl,
                deviceName: $deviceName,
                deviceModel: $deviceModel,
                notes: $notes,
            ));

            $currentStatusId = (int) $serviceRequest->status_id;

            $this->auditLogService->createAuditLog([
                'actor_id' => auth()->id() ?? $serviceRequest->user_id,
                'entity_id' => $serviceRequest->id,
                'entity_type_id' => 1,
                'action' => 'EMAIL_SENT',
                'old_status_id' => $currentStatusId,
                'new_status_id' => $currentStatusId,
                'notes' => "Status change email ({$statusLabel}) queued to {$user->email} for service request #{$serviceRequest->service_number}",
            ]);
        } catch (\Throwable $e) {
            logger()->error('Failed to queue status change notification email.', [
                'service_request_id' => $serviceRequest->id,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
