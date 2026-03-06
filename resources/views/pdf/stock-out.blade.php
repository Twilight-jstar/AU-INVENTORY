<!DOCTYPE html>
<html>
<head>
    <title>Stock Out Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #553c9a; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #553c9a; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; width: 35%; }
        .footer { margin-top: 50px; }
        .sign-box { width: 200px; border-top: 1px solid #000; text-align: center; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">ALF Inventory Disbursement System</div>
        <div><strong>STOCK OUT REPORT</strong></div>
    </div>

    <div>
        <strong>Transaction ID:</strong> OUT-{{ str_replace('out-', '', $transaction->id) }}<br>
        <strong>Date Released:</strong> {{ \Carbon\Carbon::parse($transaction->date_released)->format('F d, Y') }}
    </div>

    <table>
        <tr>
            <th>Item Description</th>
            <td><strong>{{ $transaction->item->name }}</strong></td>
        </tr>
        <tr>
            <th>Quantity Released</th>
            <td style="color: red; font-weight: bold;">- {{ $transaction->quantity }}</td>
        </tr>
        <tr>
            <th>Released To</th>
            <td>{{ $transaction->released_to }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $transaction->department ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Purpose</th>
            <td>{{ $transaction->purpose ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Processed By:</p>
        <div class="sign-box">
            <strong>{{ $transaction->released_by }}</strong><br>
            Authorized Staff
        </div>
    </div>

</body>
</html>