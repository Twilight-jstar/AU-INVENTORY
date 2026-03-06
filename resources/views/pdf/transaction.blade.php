<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 25px; }
        .school-name { font-size: 20px; font-weight: bold; margin: 0; color: #000; }
        .address { font-size: 11px; margin: 2px 0; }
        .report-title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .label { background-color: #f9f9f9; font-weight: bold; width: 35%; }
        .footer { margin-top: 40px; font-size: 10px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <table style="width: 100%; border-bottom: 2px solid #000000; padding-bottom: 10px; margin-bottom: 25px;">
        <tr>
            <td style="width: 120px; border: none; vertical-align: middle; padding-left: 10px;">
                <img src="{{ public_path('images/AUSL_logo.png') }}" style="width: 100px; height: 100px;">
            </td>
            <td style="border: none; vertical-align: middle; text-align: center;">
                <p style="font-size: 25px; font-weight: bold; margin: 0; color: #551359;">ARELLANO LAW FOUNDATION</p>
                <p style="font-size: 14px; margin: 2px 0;">Taft Avenue Corner Menlo Street, Pasay City, Metro Manila, Philippines.</p>
                <p style="font-size: 14px; margin: 0; font-weight: bold;">Inventory Management System</p>
            </td>
            <td style="width: 120px; border: none;"></td>
        </tr>
    </table>

    <div class="report-title">
        {{ $transaction->type === 'In' ? 'STOCK-IN REPORT' : 'STOCK-OUT VOUCHER' }}
    </div>

    <table class="table">
        <tr><td class="label">Reference No:</td><td>#{{ $transaction->type === 'In' ? 'IN' : 'OUT' }}-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td></tr>

        @if($transaction->type === 'In')
            <tr><td class="label">Date Received:</td><td>{{ \Carbon\Carbon::parse($transaction->date_received ?? $transaction->created_at)->format('F d, Y') }}</td></tr>
            <tr><td class="label">Supplier:</td><td>{{ $transaction->supplier->name ?? $transaction->supplier_id ?? 'N/A' }}</td></tr>
            <tr><td class="label">Product Code:</td><td>{{ $transaction->item->product_code ?? 'N/A' }}</td></tr>
            <tr><td class="label">Item Description:</td><td><strong>{{ $transaction->item->item_name ?? $transaction->item->name ?? 'N/A' }}</strong></td></tr>
            <tr><td class="label">Quantity:</td><td>{{ $transaction->quantity }} units</td></tr>
            <tr><td class="label">Unit Cost:</td><td> {{ number_format($transaction->unit_cost ?? 0, 2) }}</td></tr>
            <tr><td class="label">Received By:</td><td>{{ $transaction->received_by ?? 'N/A' }}</td></tr>
        @else
            <tr><td class="label">Date Released:</td><td>{{ \Carbon\Carbon::parse($transaction->date_released ?? $transaction->created_at)->format('F d, Y') }}</td></tr>
            <tr><td class="label">Product Code:</td><td>{{ $transaction->item->product_code ?? 'N/A' }}</td></tr>
            <tr><td class="label">Item Description:</td><td><strong>{{ $transaction->item->name ?? 'N/A' }}</strong></td></tr>
            <tr><td class="label">Quantity:</td><td>{{ $transaction->quantity }} units</td></tr>
            <tr><td class="label">Released To:</td><td>{{ $transaction->released_to ?? 'N/A' }}</td></tr>
            <tr><td class="label">Department:</td><td>{{ $transaction->department ?? 'N/A' }}</td></tr>
            <tr><td class="label">Purpose:</td><td>{{ $transaction->purpose ?? 'N/A' }}</td></tr>
            <tr><td class="label">Released By:</td><td>{{ $transaction->released_by ?? 'N/A' }}</td></tr>
        @endif

        <tr><td class="label">Notes:</td><td>{{ $transaction->note ?? 'No additional details.' }}</td></tr>
    </table>

    <div style="margin-top: 40px; font-size: 13px;">
        @if($transaction->type === 'In')
            <strong>SIGNATURE:</strong> __________________________________________
        @else
            <div style="margin-bottom: 25px;">
                <strong>RELEASED BY (Signature):</strong> ________________________________
            </div>
            <div>
                <strong>RECEIVED BY (Signature):</strong> ________________________________
            </div>
        @endif
    </div>

    <div class="footer">
        Generated on: {{ date('Y-m-d H:i:s') }} | Official AU System Record
    </div>
</body>
</html>