<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        /* PDF Page Setup */
        @page { 
            size: letter portrait; 
            margin: 30px 40px; 
        }
        
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.2; 
            margin: 0;
            padding: 0;
        }

        /* Header Section */
        .header-table { 
            width: 100%; 
            border-bottom: 2px solid #000; 
            padding-bottom: 8px; 
            margin-bottom: 15px; 
        }

        .report-title { 
            text-align: center; 
            font-size: 13px; 
            font-weight: bold; 
            text-decoration: underline; 
            margin-bottom: 20px; 
            text-transform: uppercase; 
        }

        /* Compact Transaction Containers */
        .transaction-container { 
            margin-bottom: 15px; 
            page-break-inside: avoid; 
        }

        .ref-table { 
            width: 100%; 
            background: #f9f9f9; 
            border: 1px solid #000; 
            margin-bottom: -1px; 
        }

        .ref-table td { 
            padding: 4px 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 8.5px; 
        }

        /* Main Data Table */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000; 
        }

        .main-table th { 
            text-align: left; 
            padding: 4px 6px; 
            border: 1px solid #000; 
            background: #ececec; 
            text-transform: uppercase; 
            font-size: 8px; 
        }

        .main-table td { 
            padding: 4px 6px; 
            border: 1px solid #000; 
            vertical-align: middle; 
            font-size: 9px; 
        }

        /* Signatories */
        .footer-section { 
            width: 100%; 
            margin-top: 40px; 
            page-break-inside: avoid; 
            border-collapse: collapse;
        }

        .sig-box { 
            width: 45%; 
            vertical-align: top; 
        }

        .sig-subtext { 
            font-size: 8px; 
            color: #555; 
            margin-bottom: 2px; 
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="text-align: center; vertical-align: middle;">
                <div style="display: inline-block; text-align: left;">
                    <div style="display: inline-block; vertical-align: middle; margin-right: 12px;">
                        <img src="{{ public_path('images/alf-logo-2022.png') }}" style="height: 50px; display: block;">
                    </div>
                    
                    <div style="display: inline-block; vertical-align: middle; text-align: center;">
                        <div style="font-size: 16px; font-weight: bold; color: #551359; line-height: 1.1;">
                            ARELLANO LAW FOUNDATION
                        </div>
                        <div style="font-size: 8.5px; color: #333;">
                            Taft Avenue Corner Menlo Street, Pasay City
                        </div>
                        <div style="font-size: 8.5px; color: #333;">
                            Tel No. 404-3089 to 93 | www.arellanolawfoundation.com
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="report-title">{{ $title }}</div>

    @foreach($transactions->groupBy('ref_no') as $refNo => $items)
        <div class="transaction-container">
            <table class="ref-table">
                <tr>
                    <td width="50%">REF: <span style="color: #551359;">#{{ $refNo }}</span></td>
                    <td width="50%" style="text-align: right; color: #551359;">
                        {{ \Carbon\Carbon::parse($items->first()->date)->format('F Y') }}
                    </td>
                </tr>
            </table>

            <table class="main-table">
                <thead>
                    <tr>
                        <th width="5%" style="text-align: center;">#</th>
                        <th width="8%" style="text-align: center;">Qty</th>
                        <th width="10%" style="text-align: center;">Unit</th>
                        <th width="42%">Property / Item Description</th>
                        <th width="35%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $trx)
                        <tr>
                            <td style="text-align: center; color: #777;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $trx->total_quantity }}</td>
                            <td style="text-align: center;">{{ $trx->item->unit->name ?? 'PCS' }}</td>
                            <td>
                                <strong>{{ $trx->item->name }}</strong>
                            </td>
                            <td>{{ $trx->combined_remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @if($transactions->isEmpty())
        <div style="text-align: center; padding: 30px; border: 1px dashed #ccc; text-transform: uppercase; font-size: 9px; color: #999;">
            No transactions found for the selected criteria.
        </div>
    @endif

    <table class="footer-section">
        <tr>
            <td class="sig-box" style="vertical-align: bottom;">
                <div class="sig-subtext" style="margin-bottom: 25px;">Prepared By:</div>
                
                <div style="border-bottom: 1.5px solid #000; padding-bottom: 2px; height: 18px; text-align: center;">
                    <span style="font-weight: bold; text-transform: uppercase; font-size: 10px;">
                        {{ auth()->user()->name ?? 'INVENTORY CUSTODIAN' }}
                    </span>
                </div>
                
                <div class="sig-subtext" style="margin-top: 5px; font-size: 8px; text-align: center;">
                    Property Office Representative
                </div>
            </td>

            <td style="width: 10%;"></td>

            <td class="sig-box" style="vertical-align: bottom;">
                <div class="sig-subtext" style="margin-bottom: 25px;">Received By:</div>
                
                <div style="border-bottom: 1.5px solid #000; padding-bottom: 2px; height: 18px; text-align: center;">
                    <span style="font-weight: bold; text-transform: uppercase; font-size: 10px;">
                        {!! $received_by_name ?? '&nbsp;' !!}
                    </span>
                    @if(isset($received_by_date))
                        <span style="font-weight: bold; font-size: 10px; margin: 0 4px;">/</span>
                        <span style="font-weight: bold; font-size: 10px;">
                            {{ \Carbon\Carbon::parse($received_by_date)->format('m/d/Y') }}
                        </span>
                    @endif
                </div>
                
                <div class="sig-subtext" style="margin-top: 5px; font-size: 8px; text-align: center;">
                    Signature Over Printed Name and Date
                </div>
            </td>
        </tr>
    </table>
</body>
</html>