<!DOCTYPE html>
<html>
<head>
    <title>Stock In Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #28a745; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; width: 35%; }
        .footer { margin-top: 50px; }
        .sign-box { width: 200px; border-top: 1px solid #000; text-align: center; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">ALF Inventory System</div>
        <div><strong>STOCK IN REPORT</strong></div>
    </div>

    <div>
        <strong>Control No:</strong> IN-{{ str_replace('in-', '', $transaction->id) }}<br>
        <strong>Date Received:</strong> {{ \Carbon\Carbon::parse($transaction->date_received)->format('F d, Y') }}
    </div>

    <table>
        <tr>
            <th>Item Description</th>
            <td><strong>{{ $transaction->item->name }}</strong></td>
        </tr>
        <tr>
            <th>Quantity Received</th>
            <td style="color: green; font-weight: bold;">+ {{ $transaction->quantity }}</td>
        </tr>
        <tr>
            <th>Reference No (Invoice/Receipt)</th>
            <td>{{ $transaction->reference_no ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Received By:</p>
        <div class="sign-box">
            <strong>{{ $transaction->received_by }}</strong><br>
            Staff Name
        </div>
    </div>

</body>
</html>