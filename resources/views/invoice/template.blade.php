<!DOCTYPE html>
<html>
<head>
    <title>Service Request Form - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }
        .no-border {
            border: none;
        }
        .header-logo {
            width: 150px;
        }
        .header-title {
            text-align: right;
            font-weight: bold;
        }
        .header-title div {
            margin-bottom: 2px;
        }
        .section-header {
            background-color: #d9d9d9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .label-col {
            width: 120px;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .input-box {
            height: 20px;
        }
        .large-box {
            height: 100px;
            vertical-align: top;
        }
        .checkbox-rect {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
        }
        .signature-box {
            height: 60px;
            vertical-align: bottom;
            text-align: center;
        }
        .center-text {
            text-align: center;
        }
        .grid-bg {
            /* Optional: add a grid background if really wanting to mimic the excel view precisely, 
               but usually clean white is better for print. 
               The user asked for "kek excel gini" which implies the structure/grid lines. 
               The borders on all cells achieved that. */
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table style="border: none; margin-bottom: 10px;">
        <tr style="border: none;">
            <td style="border: none; width: 50%; vertical-align: top;">
                <!-- Logo Placehoder - assuming simple text or local path if available -->
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="header-logo" style="width: 120px">
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <div style="font-weight: bold; font-size: 14px; text-decoration: underline;">A1 FORM</div>
                <div>IT - Department</div>
                <div style="font-weight: bold;">Electronic City Indonesia</div>
                <div style="font-weight: bold; font-size: 14px;">Data Request Form</div>
            </td>
        </tr>
    </table>

    <table>
        <!-- Request Info -->
        <tr>
            <td class="label-col">Request Date</td>
            <td style="width: 10px; border-right: none;">:</td>
            <td style="border-left: none;">
                {{ $serviceRequest->created_at->format('d') }} / 
                {{ $serviceRequest->created_at->format('m') }} / 
                {{ $serviceRequest->created_at->format('Y') }}
            </td>
            <td class="label-col" style="text-align: right;">Form No :</td>
            <td>{{ $invoice->invoice_number }}</td>
        </tr>
    </table>

    <table style="border-top: none;">
        <!-- User Section -->
        <tr>
            <td colspan="3" class="section-header">Fill In by User / Requester</td>
        </tr>
        <tr>
            <td class="label-col">Request Name</td>
            <td style="width: 10px; border-right: none;">:</td>
            <td style="border-left: none;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label-col">Department</td>
            <td style="width: 10px; border-right: none;">:</td>
            <td style="border-left: none;">
                <!-- Showing first department if available -->
                {{ $user->departments->first()->name ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="label-col" style="vertical-align: top;">Data <br><span style="font-weight: normal; font-size: 9px;">(Device Info)</span></td>
            <td style="width: 10px; border-right: none; vertical-align: top;">:</td>
            <td style="border-left: none; height: 150px; vertical-align: top;">
                @if(isset($device))
                    <div><strong>Brand:</strong> {{ $device->device_model->brand ?? '-' }}</div>
                    <div><strong>Model:</strong> {{ $device->device_model->model ?? '-' }}</div>
                    <div><strong>Serial Number:</strong> {{ $device->serial_number ?? '-' }}</div>
                @else
                    No Device Associated
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-col">Database</td>
            <td style="width: 10px; border-right: none;">:</td>
            <td style="border-left: none;">
                <!-- Unused for standard service request but keeping structure -->
                -
            </td>
        </tr>
        <tr>
            <td class="label-col" style="vertical-align: top;">Description of Request</td>
            <td style="width: 10px; border-right: none; vertical-align: top;">:</td>
            <td style="border-left: none; height: 60px; vertical-align: top;">
                {{ $device->complaint ?? $serviceRequest->service_request_details->first()->complaint ?? '-' }}
            </td>
        </tr>
    </table>

    <table style="border-top: none;">
        <!-- IT Section -->
        <tr>
            <td colspan="4" class="section-header">Fill In by IT / MIS team after check the Request from user / requester</td>
        </tr>
        <tr>
            <td class="label-col" style="vertical-align: top;">Action</td>
            <td style="width: 10px; border-right: none; vertical-align: top;">:</td>
            <td colspan="2" style="border-left: none; height: 120px; vertical-align: top;">
                <ul style="margin: 0; padding-left: 20px;">
                @foreach($serviceRequest->service_costs as $cost)
                    <li>
                        {{ $cost->cost_type->name ?? 'Cost' }}: {{ $cost->description ?? '-' }} 
                        (Rp {{ number_format($cost->amount, 0, ',', '.') }})
                    </li>
                @endforeach
                
                @if($serviceRequest->service_costs->isEmpty())
                    <li>Check and Verification</li>
                @endif  
                </ul>
            </td>
        </tr>
        <tr>
            <td class="label-col">Planning</td>
            <td style="width: 10px; border-right: none;">:</td>
            <td colspan="2" style="border-left: none;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 150px;">
                            {{ $serviceRequest->created_at->diffInDays($serviceRequest->updated_at) }} day(s)
                        </td>
                        <td class="label-col" style="border: none; width: 60px;">Actual</td>
                        <td style="border: none; width: 10px;">:</td>
                        <td style="border: none;">
                             {{ $serviceRequest->created_at->diffInDays($invoice->issue_date) }} day(s)
                        </td>
                         <td class="label-col" style="border: none; width: 60px;">Test Doc</td>
                        <td style="border: none; width: 10px;">:</td>
                        <td style="border: none;">
                             <span class="checkbox-rect"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label-col">Start Date</td>
            <td style="width: 10px; border-right: none;">:</td>
             <td colspan="2" style="border-left: none;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 150px;">
                             {{ $serviceRequest->created_at->format('d M Y') }}
                        </td>
                        <td class="label-col" style="border: none; width: 60px;">Finish</td>
                        <td style="border: none; width: 10px;">:</td>
                        <td style="border: none;">
                             {{ $completedAt ? $completedAt->format('d M Y') : '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label-col">Programmer</td>
            <td style="width: 10px; border-right: none;">:</td>
             <td colspan="2" style="border-left: none;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 40%;">
                             {{ $admin->name ?? '-' }}
                        </td>
                        <td class="label-col" style="border: none; width: 60px;">Analyst</td>
                        <td style="border: none; width: 10px;">:</td>
                        <td style="border: none;">
                             {{ $admin->name ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label-col">Comment</td>
            <td style="width: 10px; border-right: none;">:</td>
             <td colspan="2" style="border-left: none; height: 40px;">
                <!-- Placeholder for comments -->
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <table style="border-top: none;">
        <tr>
            <th style="width: 16%;">Requester by,<br>User/Supervisor</th>
            <th style="width: 16%;">Review by,<br>IT Manager</th>
            <th style="width: 16%;">Approved by,<br>Director</th>
            <th style="width: 16%;">Approved by,<br>Director</th>
            <th style="width: 16%;">Tested,</th>
            <th style="width: 20%;">Executed by,</th>
        </tr>
        <tr>
            <!-- Requester -->
            <td class="signature-box">
                <br><br>
                <div style="font-weight: bold; text-decoration: underline;">{{ $user->name }}</div>
                <div>Date: {{ $serviceRequest->created_at->format('d/m/Y') }}</div>
            </td>
            <!-- Review IT Manager -->
            <td class="signature-box">
                 <br><br>
                 <div style="border-bottom: 1px solid #aaa; width: 80%; margin: 0 auto;"></div>
                 <div>Date:</div>
            </td>
             <!-- Approved Director 1 -->
            <td class="signature-box">
                 <br><br>
                 <div style="border-bottom: 1px solid #aaa; width: 80%; margin: 0 auto;"></div>
                 <div>Date:</div>
            </td>
             <!-- Approved Director 2 -->
            <td class="signature-box">
                 <br><br>
                 <div style="border-bottom: 1px solid #aaa; width: 80%; margin: 0 auto;"></div>
                 <div>Date:</div>
            </td>
             <!-- Tested -->
            <td class="signature-box">
                 <br><br>
                 <div style="border-bottom: 1px solid #aaa; width: 80%; margin: 0 auto;"></div>
                 <div>Date:</div>
            </td>
             <!-- Executed -->
            <td class="signature-box">
                 <br><br>
                 <div style="font-weight: bold; text-decoration: underline;">{{ $admin->name ?? 'Admin' }}</div>
                 <div>Date: {{ $invoice->issue_date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
    
    <div style="font-size: 10px; margin-top: 5px;">Update : {{ now()->format('F Y') }}</div>

</body>
</html>
