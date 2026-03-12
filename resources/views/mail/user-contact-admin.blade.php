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

        $resolvedDeviceModel = $deviceModel ?? null;
        $resolvedDamages = is_array($damages ?? null) ? $damages : [];
        $resolvedItems = is_array($serviceRequestItems ?? null) ? $serviceRequestItems : [];
        $resolvedMessage = $userMessage ?? ($message ?? '');

        $resolvedDeviceType = $device ?? null;
        if (empty($resolvedDeviceType) && count($resolvedItems) > 0) {
            $resolvedDeviceType = $resolvedItems[0]['device_type'] ?? null;
        }
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
        @if (!empty($resolvedDeviceType))
        <tr>
            <td style="padding: 6px 0;"><strong>Device Type</strong></td>
            <td style="padding: 6px 0;">{{ $resolvedDeviceType }}</td>
        </tr>
        @endif
        @if (!empty($resolvedDeviceModel))
        <tr>
            <td style="padding: 6px 0;"><strong>Device Model</strong></td>
            <td style="padding: 6px 0;">{{ $resolvedDeviceModel }}</td>
        </tr>
        @endif
    </table>

    @if (count($resolvedItems) > 0)
        <h3 style="margin: 18px 0 8px;">Detail Perangkat</h3>
        <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px; border: 1px solid #e5e7eb;">
            <thead>
                <tr>
                    <th align="left" style="padding: 10px; border-bottom: 1px solid #e5e7eb;">Device Type</th>
                    <th align="left" style="padding: 10px; border-bottom: 1px solid #e5e7eb;">Model</th>
                    <th align="left" style="padding: 10px; border-bottom: 1px solid #e5e7eb;">Serial</th>
                    <th align="left" style="padding: 10px; border-bottom: 1px solid #e5e7eb;">Keluhan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resolvedItems as $item)
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f3f4f6;">{{ $item['device_type'] ?? '-' }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #f3f4f6;">{{ $item['device_model'] ?? '-' }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #f3f4f6;">{{ $item['serial_number'] ?? '-' }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #f3f4f6;">{{ $item['complaint'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($resolvedDamages) > 0)
        <h3 style="margin: 18px 0 8px;">Kerusakan</h3>
        <ul style="margin: 0 0 14px; padding-left: 18px;">
            @foreach ($resolvedDamages as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif

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
