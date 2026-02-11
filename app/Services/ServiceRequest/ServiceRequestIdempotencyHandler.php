<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestDetail;
use App\Models\Device;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Enums\ServiceRequestStatusCode;

class ServiceRequestIdempotencyHandler{
    public function ensureDeviceIdempotency(array $details, ?int $excludeServiceRequestId = null): void
    {
        if (empty($details)) {
            return;
        }

        $serialNumbers = [];
        foreach ($details as $detail) {
            if (!empty($detail['serial_number'])) {
                $serialNumbers[] = trim((string) $detail['serial_number']);
            }
        }

        $serialNumbers = array_values(array_unique(array_filter($serialNumbers)));
        $serialToDeviceId = [];

        if (!empty($serialNumbers)) {
            $serialToDeviceId = Device::query()
                ->whereIn('serial_number', $serialNumbers)
                ->get(['id', 'serial_number'])
                ->mapWithKeys(function (Device $device) {
                    return [strtolower($device->serial_number) => $device->id];
                })
                ->all();
        }

        $deviceIds = [];
        $seen = [];
        $duplicateKeys = [];

        foreach ($details as $detail) {
            $deviceId = $detail['device_id'] ?? null;
            $serial = isset($detail['serial_number']) ? trim((string) $detail['serial_number']) : null;
            $serialKey = $serial !== null ? strtolower($serial) : null;

            if (!$deviceId && $serialKey && isset($serialToDeviceId[$serialKey])) {
                $deviceId = (int) $serialToDeviceId[$serialKey];
            }

            if ($deviceId) {
                $key = 'device:' . (int) $deviceId;
            } elseif ($serialKey) {
                $key = 'serial:' . $serialKey;
            } else {
                continue;
            }

            if (isset($seen[$key])) {
                $duplicateKeys[] = $key;
                continue;
            }

            $seen[$key] = true;

            if ($deviceId) {
                $deviceIds[] = (int) $deviceId;
            }
        }

        if (!empty($duplicateKeys)) {
            $labels = array_map(function ($key) {
                if (str_starts_with($key, 'serial:')) {
                    return substr($key, 7);
                }
                return 'device_id ' . substr($key, 7);
            }, $duplicateKeys);

            throw ValidationException::withMessages([
                'details' => [
                    'Device yang sama tidak boleh lebih dari satu dalam satu request: ' . implode(', ', array_values(array_unique($labels))) . '.'
                ],
            ]);
        }

        $deviceIds = array_values(array_unique(array_filter($deviceIds)));

        if (empty($deviceIds)) {
            return;
        }

        $query = ServiceRequestDetail::query()
            ->whereIn('device_id', $deviceIds)
            ->whereHas('service_request.status', function ($q) {
                $q->whereNotIn('code', [
                    ServiceRequestStatusCode::COMPLETED->value,
                    ServiceRequestStatusCode::CANCELLED->value
                ]);
            });

        if ($excludeServiceRequestId !== null) {
            $query->where('service_request_id', '!=', $excludeServiceRequestId);
        }

        $conflicts = $query->with([
            'device:id,serial_number',
            'service_request:id,service_number,status_id',
            'service_request.status:id,code',
        ])->get();

        if ($conflicts->isEmpty()) {
            return;
        }

        $messages = $conflicts->map(function (ServiceRequestDetail $detail) {
            $serial = $detail->device?->serial_number ?? ('device_id ' . $detail->device_id);
            $serviceNumber = $detail->service_request?->service_number ?? ('#' . $detail->service_request_id);
            $statusCode = $detail->service_request?->status?->code ?? 'UNKNOWN';

            return "Device {$serial} masih punya service request aktif ({$serviceNumber}, {$statusCode}).";
        })->unique()->values()->all();

        throw ValidationException::withMessages([
            'details' => $messages,
        ]);
    }
}