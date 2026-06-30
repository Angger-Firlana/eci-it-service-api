<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Servis {{ $statusLabel }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111;">

    <h2 style="margin: 0 0 12px;">
        @if ($statusCode === 'COMPLETED')
            Servis Selesai &#x2705;
        @elseif ($statusCode === 'REJECTED_BY_ABOVE')
            Servis Ditolak &#x274C;
        @else
            Status Servis Diperbarui
        @endif
    </h2>

    <p style="margin: 0 0 10px;">Halo <strong>{{ $userName }}</strong>,</p>

    @if ($statusCode === 'COMPLETED')
    <p style="margin: 0 0 14px;">
        Servis kamu telah <strong>selesai</strong> dikerjakan. Berikut detail servisnya:
    </p>
    @elseif ($statusCode === 'REJECTED_BY_ABOVE')
    <p style="margin: 0 0 14px;">
        Permintaan vendor untuk servis kamu telah <strong>ditolak</strong> oleh atasan. Berikut detail servisnya:
    </p>
    @else
    <p style="margin: 0 0 14px;">
        Status servis kamu telah diperbarui. Berikut detail servisnya:
    </p>
    @endif

    <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        <tr>
            <td style="padding: 6px 0; width: 160px;"><strong>Service Number</strong></td>
            <td style="padding: 6px 0;">{{ $serviceNumber }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0;"><strong>Status</strong></td>
            <td style="padding: 6px 0;">
                <span style="
                    display: inline-block;
                    padding: 2px 10px;
                    border-radius: 12px;
                    font-size: 13px;
                    font-weight: 600;
                    @if ($statusCode === 'COMPLETED')
                        background: #d1fae5; color: #065f46;
                    @elseif ($statusCode === 'REJECTED_BY_ABOVE')
                        background: #fee2e2; color: #991b1b;
                    @else
                        background: #e5e7eb; color: #374151;
                    @endif
                ">
                    {{ $statusLabel }}
                </span>
            </td>
        </tr>
        @if ($deviceName || $deviceModel)
        <tr>
            <td style="padding: 6px 0;"><strong>Perangkat</strong></td>
            <td style="padding: 6px 0;">
                {{ implode(' — ', array_filter([$deviceName, $deviceModel])) }}
            </td>
        </tr>
        @endif
        @if ($notes)
        <tr>
            <td style="padding: 6px 0; vertical-align: top;"><strong>Catatan</strong></td>
            <td style="padding: 6px 0; white-space: pre-wrap;">{{ $notes }}</td>
        </tr>
        @endif
    </table>

    @if ($serviceRequestUrl)
    <div style="margin-top: 18px;">
        <p style="margin: 0;">
            Lihat detail servis:
            <a href="{{ $serviceRequestUrl }}" style="color: #2563eb;">
                {{ $serviceRequestUrl }}
            </a>
        </p>
    </div>
    @endif

    <p style="margin-top: 18px;">Terima kasih.<br>— Tim ECI IT Service</p>

</body>
</html>
