<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: letter portrait; margin: 30px; }
        
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 25px; }
        .school-name { font-size: 20px; font-weight: bold; margin: 0; color: #000; }
        .address { font-size: 11px; margin: 2px 0; }
        .report-title { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin-bottom: 20px; letter-spacing: 1px; }
        
        .info-table { width: 100%; margin-bottom: 10px; font-size: 11px; font-weight: bold; border-collapse: collapse;}
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 6px; text-align: center; word-wrap: break-word; }
        .items-table th { background-color: #f9f9f9; font-weight: bold; }
        .items-table td.desc { text-align: left; }
        
        .footer { margin-top: 20px; font-size: 10px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        .ref-no { position: absolute; top: -10px; right: 0; font-size: 9px; }
    </style>
</head>

<body>
    <div class="ref-no">
        Order #{{ $transaction->type === 'In' ? 'IN' : 'OUT' }}-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} - Disbursement System
    </div>

    <table style="width: 100%; border-bottom: 2px solid #000000; padding-bottom: 5px; margin-bottom: 15px;">
        <tr>
            <td style="width: 100px; border: none; vertical-align: middle; padding-left: 10px;">
                <img src="{{ public_path('images/AUSL_logo.png') }}" style="width: 70px; height: 70px;">
            </td>
            <td style="border: none; vertical-align: middle; text-align: center;">
                <p style="font-size: 18px; font-weight: bold; margin: 0; color: #551359;">ARELLANO LAW FOUNDATION</p>
                <p style="font-size: 11px; margin: 2px 0;">Taft Avenue Corner Menlo Street, Pasay City, Metro Manila</p>
                <p style="font-size: 11px; margin: 0; font-weight: bold;">Tel.No.404-3089 to 93</p>
            </td>
            <td style="width: 100px; border: none;"></td>
        </tr>
    </table>

    <div class="report-title">
        {{ $transaction->type === 'In' ? 'STOCK-IN REPORT' : 'STOCK ISSUANCE FORM' }}
    </div>

    <table class="info-table">
        <tr>
            <td style="text-align: left;">
                @if($transaction->type === 'In')
                    Supplier: {{ $transaction->supplier->name ?? $transaction->supplier_id ?? 'N/A' }}
                @else
                    Issued To: {{ $transaction->department ?? $transaction->released_to }}
                @endif
            </td>
            <td style="text-align: right;">
                Date: {{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">Product Code</th>
                <th width="10%">Qty</th>
                <th width="10%">Unit</th>
                <th width="25%">Item Description</th>
                <th width="30%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="font-weight: bold;">{{ $transaction->item->product_code ?? 'N/A' }}</td>
                <td>{{ $transaction->quantity }}</td>
                <td>PCS</td>
                <td class="desc">{{ $transaction->item->name ?? 'N/A' }}</td>
                <td>{{ $transaction->purpose ?? $transaction->note ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 30px; font-size: 11px; font-weight: bold;">
        <tr>
            @if($transaction->type === 'In')
                {{-- STOCK IN: Received By Lang --}}
                <td style="text-align: left; width: 100%;">
                    Received By : <span style="text-decoration: underline;">{{ $transaction->received_by ?? 'Property Custodian' }}</span>
                </td>
            @else
                {{-- STOCK OUT: Prepared at Received --}}
                <td style="text-align: left; width: 50%;">
                    Prepared By : <span style="text-decoration: underline;">{{ $transaction->released_by ?? 'Property Custodian' }}</span>
                </td>
                <td style="text-align: right; width: 50%;">
                    Received By : _______________________
                </td>
            @endif
        </tr>
    </table>

    <div class="footer">
        Generated on: {{ date('Y-m-d H:i:s') }} | Official AU System Record
    </div>
</body>
</html>