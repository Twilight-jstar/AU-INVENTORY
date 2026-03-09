<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Issuance Form</title>
    <style>
        
        @page {
            size: a5 landscape; 
            margin: 25px;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px; 
            color: #000;
        }

        /* HEADER STYLING */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        .company-address {
            font-size: 10px;
            margin: 0;
        }
        .form-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        /* META INFO (Department & Date) */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: bold;
        }

        /* ITEMS TABLE STYLING */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }
        .items-table th {
            font-weight: bold;
        }
        .items-table td.desc {
            text-align: left; 
        }

        /* SIGNATURES */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            font-size: 11px;
            font-weight: bold;
        }

        /* REFERENCE NUMBER (Top Right) */
        .ref-no {
            position: absolute;
            top: -10px;
            right: 0;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <div class="ref-no">
        Order OUT-{{ str_replace('out-', '', $transaction->id) }} - Disbursement System
    </div>

    <table class="header-table">
        <tr>
            <td width="20%" style="text-align: right; padding-right: 15px;">
                </td>
            <td width="60%" style="text-align: center; line-height: 1.2;">
                <div class="company-name">ARELLANO LAW FOUNDATION</div>
                <div class="company-address">Taft Ave, Cor. Menlo St. Pasay City</div>
                <div class="company-address">Tel. No. 404-3089 to 93</div>
            </td>
            <td width="20%"></td>
        </tr>
    </table>

    <div class="form-title">STOCK ISSUANCE FORM</div>

    <table class="info-table">
        <tr>
            <td style="text-align: left;">Issued To: {{ $transaction->department ?? $transaction->released_to }}</td>
            <td style="text-align: right;">Request Date: {{ \Carbon\Carbon::parse($transaction->date_released)->format('M d, Y') }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Quantity</th>
                <th width="15%">Unit</th>
                <th width="45%">Description</th>
                <th width="20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $transaction->quantity }}</td>
                <td>PCS</td> <td class="desc">{{ $transaction->item->name }}</td>
                <td>{{ $transaction->purpose ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="text-align: left; width: 50%;">
                Prepared By : <span style="text-decoration: underline;">{{ $transaction->released_by }}</span>
            </td>
            <td style="text-align: right; width: 50%;">
                Received By : _______________________
            </td>
        </tr>
    </table>

</body>
</html>