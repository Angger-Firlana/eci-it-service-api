<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Contact Admin</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111;">
    @php
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $loginUrl = $frontendUrl !== '' ? $frontendUrl.'/login' : null;

        $resolvedServiceRequestUrl = $serviceRequestUrl ?? null;
        if (!$resolvedServiceRequestUrl && isset($serviceRequestId) && $serviceRequestId) {
            $resolvedServiceRequestUrl = $frontendUrl !== '' ? $frontendUrl.'/service-requests/'.$serviceRequestId : null;
        }

        $resolvedDamages = is_array($damages ?? null) ? $damages : [];
        $resolvedItems = is_array($serviceRequestItems ?? null) ? $serviceRequestItems : [];
        $resolvedMessage = $userMessage ?? ($message ?? '');

        $resolvedDeviceType = $device ?? null;
        if (empty($resolvedDeviceType) && count($resolvedItems) > 0) {
            $resolvedDeviceType = $resolvedItems[0]['device_type'] ?? null;
        }

        $resolvedComplaint = null;
        if (count($resolvedItems) > 0) {
            $resolvedComplaint = $resolvedItems[0]['complaint'] ?? null;
        }
        if ((!is_string($resolvedComplaint) || trim($resolvedComplaint) === '') && count($resolvedDamages) > 0) {
            $resolvedComplaint = implode(', ', $resolvedDamages);
        }
        $resolvedComplaint = is_string($resolvedComplaint) && trim($resolvedComplaint) !== '' ? trim($resolvedComplaint) : null;

        $deviceHeadlineParts = array_values(array_filter([
            is_string($resolvedDeviceType) && trim($resolvedDeviceType) !== '' ? trim($resolvedDeviceType) : null,
            $resolvedComplaint,
        ]));
        $deviceHeadline = count($deviceHeadlineParts) > 0 ? implode(', ', $deviceHeadlineParts) : null;
    @endphp

    <h2 style="margin: 0 0 12px;">Permintaan Servis Baru</h2>
    <p style="margin: 0 0 10px;">Halo Tim IT Service,</p>
    <p style="margin: 0 0 14px;">Ada laporan permintaan servis baru dari user dengan detail berikut.</p>

    <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        @if (!empty($serviceNumber))
        <tr>
            <td style="padding: 6px 0; width: 160px;"><strong>Service Number</strong></td>
            <td style="padding: 6px 0;">{{ $serviceNumber }}</td>
        </tr>
        @endif
        @if (!empty($serviceRequestId))
        <tr>
            <td style="padding: 6px 0; width: 160px;"><strong>Service Request</strong></td>
            <td style="padding: 6px 0;">#{{ $serviceRequestId }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 6px 0; width: 160px;"><strong>Nama</strong></td>
            <td style="padding: 6px 0;">{{ $name }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0;"><strong>Email</strong></td>
            <td style="padding: 6px 0;">{{ $email }}</td>
        </tr>
    </table>

    <div style="margin: 18px 0 8px;">
        <h3 style="margin: 0 0 8px;">Perangkat</h3>
        <div style="border: 1px solid #e5e7eb; background: #f9fafb; padding: 14px; border-radius: 12px; max-width: 640px;">
            <div style="font-size: 16px; font-weight: 700; margin: 0 0 8px;">
                {{ $deviceHeadline ?? '-' }}
            </div>
            <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="padding: 4px 0; width: 120px; color: #6b7280;"><strong>Type</strong></td>
                    <td style="padding: 4px 0;">{{ $resolvedDeviceType ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;"><strong>Keluhan</strong></td>
                    <td style="padding: 4px 0;">{{ $resolvedComplaint ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <h3 style="margin: 18px 0 8px;">Pesan</h3>
    <div style="white-space: pre-wrap; border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px;">
        {{ $resolvedMessage }}
    </div>

    <div style="margin-top: 18px;">
        @if (!empty($loginUrl))
            <p style="margin: 0 0 6px;">
                Login aplikasi: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
            </p>
        @endif
        @if (!empty($resolvedServiceRequestUrl))
            <p style="margin: 0;">
                Detail Service Request: <a href="{{ $resolvedServiceRequestUrl }}">{{ $resolvedServiceRequestUrl }}</a>
            </p>
        @endif
    </div>

    <p style="margin-top: 18px;">Terima kasih.</p>
</body>
</html>
