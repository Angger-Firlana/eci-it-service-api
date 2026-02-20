<?php

namespace App\Services\ContactAdmin;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserContactAdmin;
use RuntimeException;

class ContactAdminMailservice
{
    public function queue(array $data): void
    {
        $adminEmail = $this->adminEmail();
        $attachmentPath = $this->attachmentPath($data);

        [
            $device,
            $deviceModel,
            $damages,
            $serviceRequestId,
            $serviceRequestUrl,
            $serviceNumber,
            $serviceRequestItems,
        ] = $this->serviceRequestContext($data);

        Mail::to($adminEmail)->queue(new UserContactAdmin(
            name: $data['name'],
            email: $data['email'],
            userMessage: $data['message'],
            attachmentPath: $attachmentPath,
            device: $device,
            deviceModel: $deviceModel,
            damages: $damages,
            serviceRequestId: $serviceRequestId,
            serviceRequestUrl: $serviceRequestUrl,
            serviceNumber: $serviceNumber,
            serviceRequestItems: $serviceRequestItems,
        ));
    }

    public function sendNow(array $data): void
    {
        $adminEmail = $this->adminEmail();
        $attachmentPath = $this->attachmentPath($data);

        [
            $device,
            $deviceModel,
            $damages,
            $serviceRequestId,
            $serviceRequestUrl,
            $serviceNumber,
            $serviceRequestItems,
        ] = $this->serviceRequestContext($data);

        Mail::to($adminEmail)->sendNow(new UserContactAdmin(
            name: $data['name'],
            email: $data['email'],
            userMessage: $data['message'],
            attachmentPath: $attachmentPath,
            device: $device,
            deviceModel: $deviceModel,
            damages: $damages,
            serviceRequestId: $serviceRequestId,
            serviceRequestUrl: $serviceRequestUrl,
            serviceNumber: $serviceNumber,
            serviceRequestItems: $serviceRequestItems,
        ));
    }

    private function adminEmail(): string
    {
        $adminEmail = config('mail.admin_email');

        if (!is_string($adminEmail) || trim($adminEmail) === '') {
            throw new RuntimeException('ADMIN_MAIL is not configured.');
        }

        return $adminEmail;
    }

    private function attachmentPath(array $data): ?string
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
    private function serviceRequestContext(array $data): array
    {
        $device = $data['device'] ?? null;
        $device = is_string($device) && trim($device) !== '' ? $device : null;

        $deviceModel = $data['device_model'] ?? ($data['deviceModel'] ?? null);
        $deviceModel = is_string($deviceModel) && trim($deviceModel) !== '' ? $deviceModel : null;

        $damages = $data['damages'] ?? [];
        if (!is_array($damages)) {
            $damages = [];
        }
        $damages = array_values(array_filter(array_map(static function ($value) {
            if (!is_string($value)) {
                return null;
            }
            $value = trim($value);
            return $value === '' ? null : $value;
        }, $damages)));

        $serviceRequestId = $data['service_request_id'] ?? ($data['serviceRequestId'] ?? null);
        $serviceRequestId = is_int($serviceRequestId) ? $serviceRequestId : (is_numeric($serviceRequestId) ? (int) $serviceRequestId : null);

        $serviceRequestUrl = $data['service_request_url'] ?? ($data['serviceRequestUrl'] ?? null);
        $serviceRequestUrl = is_string($serviceRequestUrl) && trim($serviceRequestUrl) !== '' ? $serviceRequestUrl : null;

        $serviceNumber = null;
        $serviceRequestItems = [];

        // Only hit the DB when we need to enrich missing info.
        if ($serviceRequestId !== null && ($device === null || $deviceModel === null || count($damages) === 0)) {
            $serviceRequest = ServiceRequest::query()
                ->with(['service_request_details.device.device_model.device_type'])
                ->find($serviceRequestId);

            if ($serviceRequest) {
                $serviceNumber = is_string($serviceRequest->service_number) ? $serviceRequest->service_number : null;

                foreach ($serviceRequest->service_request_details as $detail) {
                    $deviceTypeName = $detail->device?->device_model?->device_type?->name;
                    $brand = $detail->device?->device_model?->brand;
                    $model = $detail->device?->device_model?->model;
                    $serialNumber = $detail->device?->serial_number;

                    $deviceModelName = null;
                    if (is_string($brand) && trim($brand) !== '' && is_string($model) && trim($model) !== '') {
                        $deviceModelName = trim($brand).' '.trim($model);
                    } elseif (is_string($model) && trim($model) !== '') {
                        $deviceModelName = trim($model);
                    }

                    $serviceRequestItems[] = [
                        'device_type' => is_string($deviceTypeName) && trim($deviceTypeName) !== '' ? $deviceTypeName : null,
                        'device_model' => $deviceModelName,
                        'serial_number' => is_string($serialNumber) && trim($serialNumber) !== '' ? $serialNumber : null,
                        'complaint' => is_string($detail->complaint) && trim($detail->complaint) !== '' ? $detail->complaint : null,
                    ];
                }

                if ($device === null && isset($serviceRequestItems[0]['device_type'])) {
                    $device = $serviceRequestItems[0]['device_type'];
                }

                if ($deviceModel === null && isset($serviceRequestItems[0]['device_model'])) {
                    $deviceModel = $serviceRequestItems[0]['device_model'];
                }

                if (count($damages) === 0) {
                    $damages = array_values(array_filter(array_map(static function ($item) {
                        $complaint = $item['complaint'] ?? null;
                        if (!is_string($complaint)) {
                            return null;
                        }
                        $complaint = trim($complaint);
                        return $complaint === '' ? null : $complaint;
                    }, $serviceRequestItems)));
                }
            }
        }

        return [$device, $deviceModel, $damages, $serviceRequestId, $serviceRequestUrl, $serviceNumber, $serviceRequestItems];
    }


    public function sendAdminNotification($serviceRequestId, $actorName, $actorEmail):void
    {
        try {
            $this->queue([
                'name' => $actorName,
                'email' => $actorEmail,
                'message' => 'A new service request has been created and requires review.',
                'service_request_id' => $serviceRequestId,
            ]);
        } catch (Throwable $e) {
            logger()->error('Failed to queue admin notification email for service request.', [
                'service_request_id' => $serviceRequestId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
