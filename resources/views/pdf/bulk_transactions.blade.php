<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        @page { size: letter portrait; margin: 30px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        
        /* Reference Number sa Taas */
        .ref-no { 
            position: absolute; 
            top: -10px; 
            right: 0; 
            font-size: 9px; 
            font-weight: bold;
            color: #666;
        }

        /* Header Table */
        .header-table { 
            width: 100%; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .school-name { 
            font-size: 18px; 
            font-weight: bold; 
            color: #551359; 
            margin: 0;
            line-height: 1.2;
        }
        
        .header-text { text-align: center; vertical-align: middle; }

        .report-title { 
            text-align: center; 
            font-size: 14px; 
            font-weight: bold; 
            text-decoration: underline; 
            margin: 10px 0; 
            text-transform: uppercase; 
        }
        
        /* Main Table Styles - New Column Arrangement */
        .main-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .main-table th { 
            background-color: #f1f5f9; 
            color: #334155; 
            font-size: 9px; 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-transform: uppercase; 
        }
        .main-table td { 
            border: 1px solid #ccc; 
            padding: 6px; 
            vertical-align: middle; 
            word-wrap: break-word; 
        }
        
        .text-center { text-align: center; }

        /* Signature Section - Pantay na Layout */
        .signature-section {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-section td {
            width: 50%;
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .sig-container { margin-top: 30px; }

        .footer { 
            position: fixed; 
            bottom: -10px; 
            width: 100%; 
            font-size: 8px; 
            text-align: center; 
            color: #94a3b8; 
            border-top: 1px solid #eee; 
            padding-top: 5px; 
        }
    </style>
</head>
<body>

    <div class="ref-no">
        Report Code: BULK-{{ now()->format('Ymd') }}-{{ rand(100,999) }} | Official Record
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 80px; text-align: left;">
                <img src="{{ public_path('images/AUSL_logo.png') }}" style="height: 75px; width: 75px;">
            </td>
            <td class="header-text">
                <div class="school-name">ARELLANO LAW FOUNDATION</div>
                <div style="font-size: 10px; margin-top: 2px;">Taft Avenue Corner Menlo Street, Pasay City, Metro Manila</div>
                <div style="font-size: 10px; font-weight: bold; margin-top: 2px;">Tel. No. 404-3089 to 93</div>
            </td>
            <td style="width: 80px;"></td>
        </tr>
    </table>

    <div class="report-title">{{ $title }}</div>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 11px;">
                @if(isset($department))
                    Issued To: <span style="color: #551359;">{{ strtoupper($department) }}</span>
                @else
                    Office/Supplier: General Inventory
                @endif
            </td>
            <td style="text-align: right; font-weight: bold; font-size: 10px;">
                Date Generated: {{ now()->format('M d, Y h:i A') }}
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="18%">Product Code</th>
                <th width="8%">Qty</th>
                <th width="12%">Unit</th>
                <th width="32%">Item Description</th>
                <th width="25%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center" style="font-family: monospace; font-weight: bold;">
                        {{ $trx->product_code }}
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ number_format($trx->total_quantity, 0) }}
                    </td>
                    <td class="text-center">{{ $trx->item->unit->name ?? 'PCS' }}</td>
                    <td style="text-transform: uppercase;">{{ $trx->item->name }}</td>
                    <td style="font-size: 8px;">
                        {{ $trx->combined_remarks }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-section">
        <tr>
            <td>
                <div style="font-weight: bold;">Prepared By:</div>
                <div class="sig-container">
                    <span style="text-decoration: underline; text-transform: uppercase; font-weight: bold;">{{ auth()->user()->name ?? 'Property Custodian' }}</span>
                    <div style="font-size: 9px; font-weight: normal; margin-top: 3px;">Property Office Representative</div>
                </div>
            </td>
            <td>
                <div style="font-weight: bold;">Received By:</div>
                <div class="sig-container">
                    <span>____________________________________</span>
                    <div style="font-size: 9px; font-weight: normal; margin-top: 3px;">Signature over Printed Name / Date</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by AU Inventory Management System | Page <script type="text/php">echo $PAGE_NUM;</script> of <script type="text/php">echo $PAGE_COUNT;</script>
    </div>

</body>
</html>