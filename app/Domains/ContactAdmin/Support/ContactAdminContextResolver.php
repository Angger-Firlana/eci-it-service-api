<?php

namespace App\Domains\ContactAdmin\Support;

use App\Models\ServiceRequest;

class ContactAdminContextResolver
{
    /**
     * Get active IT email recipients from the set_email_it table.
     * Falls back to ADMIN_MAIL and MANAGER_MAIL from .env if no recipients
     * are configured in the database.
     *
     * @return array<int, string>
     */
    public function itEmails(): array
    {
        $emails = \App\Models\SetEmailIt::with('user:id,email')
            ->where('is_active', true)
            ->get()
            ->pluck('user.email')
            ->filter()
            ->values()
            ->toArray();

        if (empty($emails)) {
            return array_values(array_filter([
                config('mail.admin_email'),
                config('mail.manager_email'),
            ], fn ($v) => is_string($v) && trim($v) !== ''));
        }

        return $emails;
    }

    public function attachmentPath(array $data): ?string
    {
        $attachmentPath = $data['attachmentPath'] ?? null;

        if (!is_string($attachmentPath) || trim($attachmentPath) === '') {
            return null;
        }

        return $attachmentPath;
    }

    /**
     * @return array{0:?string,1:?string,2:array<int,string>,3:?int,4:?string,5:?string,6:array<int,array<string, mixed>>}
     */
    public function serviceRequestContext(array $data): array
    {
        $serviceRequestId = $data['service_request_id'] ?? ($data['serviceRequestId'] ?? null);
        $serviceRequestId = is_int($serviceRequestId) ? $serviceRequestId : (is_numeric($serviceRequestId) ? (int) $serviceRequestId : null);

        $serviceRequestUrl = $data['service_request_url'] ?? ($data['serviceRequestUrl'] ?? null);
        $serviceRequestUrl = is_string($serviceRequestUrl) && trim($serviceRequestUrl) !== '' ? $serviceRequestUrl : null;

        $serviceNumber = null;
        $device = null;
        $deviceModel = null;
        $damages = [];
        $serviceRequestItems = [];

        // Primary source: service_request_details[0] (single item for now).
        if ($serviceRequestId !== null) {
            $serviceRequest = ServiceRequest::query()
                ->with(['service_request_details.device.device_model.device_type'])
                ->find($serviceRequestId);

            if ($serviceRequest) {
                $serviceNumber = is_string($serviceRequest->service_number) ? $serviceRequest->service_number : null;

                $detail = $serviceRequest->service_request_details->first();

                if ($detail) {
                    $deviceTypeName = $detail->device_type?->name;
                    $serialNumber = $detail->device?->serial_number;
                    $complaint = $detail->complaint;

                    $device = is_string($deviceTypeName) && trim($deviceTypeName) !== '' ? trim($deviceTypeName) : null;

                    $resolvedComplaint = is_string($complaint) && trim($complaint) !== '' ? trim($complaint) : null;
                    if ($resolvedComplaint !== null) {
                        $damages = [$resolvedComplaint];
                    }

                    $serviceRequestItems = [[
                        'device_type' => $device,
                        'serial_number' => is_string($serialNumber) && trim($serialNumber) !== '' ? trim($serialNumber) : null,
                        'complaint' => $resolvedComplaint,
                    ]];
                }
            }
        } else {
            // Fallback for manual contact-admin payloads without service_request_id.
            $fallbackDevice = $data['device'] ?? null;
            $device = is_string($fallbackDevice) && trim($fallbackDevice) !== '' ? trim($fallbackDevice) : null;

            $fallbackDamages = $data['damages'] ?? [];
            if (is_array($fallbackDamages)) {
                $damages = array_values(array_filter(array_map(static function ($value) {
                    if (!is_string($value)) {
                        return null;
                    }
                    $value = trim($value);
                    return $value === '' ? null : $value;
                }, $fallbackDamages)));
            }
        }

        return [$device, $deviceModel, $damages, $serviceRequestId, $serviceRequestUrl, $serviceNumber, $serviceRequestItems];
    }
}
