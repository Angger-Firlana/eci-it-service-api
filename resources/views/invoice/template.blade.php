<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            max-width: 800px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0056b3;
        }
        .header h1 {
            margin: 0;
            color: #0056b3;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header p {
            margin: 5px 0 0;
            color: #777;
            font-size: 16px;
        }
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        .invoice-details-row {
            display: table-row;
        }
        .invoice-details-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .client-info strong {
            font-size: 1.1em;
            color: #000;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta table {
            margin-left: auto;
            border-collapse: collapse;
        }
        .invoice-meta td {
            padding: 3px 0 3px 15px;
        }
        .invoice-meta .label {
            color: #777;
            font-weight: bold;
        }
        .section-title {
            background-color: #f7f9fc;
            padding: 10px 15px;
            font-weight: bold;
            color: #0056b3;
            border-left: 5px solid #0056b3;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .device-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .device-table th {
            background-color: #0056b3;
            color: #fff;
            padding: 12px 15px;
            text-align: left;
            font-weight: normal;
        }
        .device-table td {
            border: 1px solid #e9ecef;
            padding: 12px 15px;
        }
        .device-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .signatures {
            width: 100%;
            margin-top: 80px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 40px;
        }
        .signature-line {
            border-bottom: 1px solid #aaa;
            margin-bottom: 10px;
            height: 1px;
        }
        .signature-name {
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        .signature-role {
            color: #777;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SERVICE INVOICE</h1>
            <p>{{ $invoice->invoice_number }}</p>
        </div>

        <div class="invoice-details">
            <div class="invoice-details-row">
                <div class="invoice-details-cell client-info">
                    <div class="label" style="color:#777; margin-bottom:5px; text-transform:uppercase; font-size:11px;">Billed To</div>
                    <strong>{{ $user->name }}</strong><br>
                    {{ $user->email }}
                </div>
                <div class="invoice-details-cell invoice-meta">
                    <table>
                        <tr>
                            <td class="label">Issue Date:</td>
                            <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Due Date:</td>
                            <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Service Req #:</td>
                            <td>{{ $serviceRequest->service_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">Service Date:</td>
                            <td>{{ $serviceRequest->request_date->format('d M Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="section-title">Device Information</div>
        
        @if($device)
        <table class="device-table">
            <thead>
                <tr>
                    <th width="30%">Attribute</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Device Model</strong></td>
                    <td>{{ $device->device_model->brand ?? '' }} {{ $device->device_model->model ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>Serial Number</strong></td>
                    <td>{{ $device->serial_number }}</td>
                </tr>
                <tr>
                    <td><strong>Reported Complaint</strong></td>
                    <td>{{ $device->complaint ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="padding: 20px; text-align: center; color: #777; background: #f9f9f9;">No device details available.</p>
        @endif

        <table class="signatures">
            <tr>
                <td>
                    <div style="height: 60px;"></div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $user->name }}</div>
                    <div class="signature-role">Customer Signature</div>
                </td>
                <td>
                    <div style="height: 60px;"></div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $admin->name ?? 'Administrator' }}</div>
                    <div class="signature-role">Authorized Signature</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
