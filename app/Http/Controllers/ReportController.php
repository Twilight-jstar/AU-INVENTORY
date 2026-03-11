<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Generate and download the inventory report.
     * If the request wants JSON, it returns the raw data instead of a file.
     */
    public function download(Request $request)
    {
        $items = Item::with(['category', 'unit'])->get();

        // API Support: If the client just wants the data in JSON format
        if ($request->wantsJson()) {
            return response()->json([
                'report_name' => 'ALF Inventory Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'data' => $items->map(function($item) {
                    return [
                        'product_code' => $item->product_code,
                        'name' => $item->name,
                        'category' => $item->category->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'min_stock' => $item->min_stock,
                        'unit' => $item->unit->name ?? 'N/A',
                        'status' => ($item->quantity <= $item->min_stock) ? 'CRITICAL' : 'HEALTHY'
                    ];
                })
            ]);
        }

        // Default: Web behavior (Excel Download)
        $fileName = 'ALF_Inventory_Report_' . date('Y-m-d_His') . '.xls';
        
        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($items) {
            $logoUrl = asset('images/ALF Logo 2022.png');

            echo '<table border="1" style="font-family: Arial, sans-serif;">';
            echo '<tr><td colspan="7"></td></tr>';
            echo '<tr><td colspan="7"></td></tr>';
            
            // HEADER WITH LOGO
            echo '<tr>';
            echo '<th rowspan="2" style="text-align: center; vertical-align: middle; width: 80px;">';
            echo '<img src="' . $logoUrl . '" width="65" height="65" alt="ALF Logo">';
            echo '</th>';
            echo '<th colspan="6" style="text-align: center; font-size: 22px; font-weight: bold; vertical-align: bottom;">ARELLANO UNIVERSITY - SCHOOL OF LAW</th>';
            echo '</tr>';
            
            echo '<tr>';
            echo '<th colspan="6" style="text-align: center; font-size: 16px; font-weight: bold; vertical-align: top;">ALF Inventory Management System</th>';
            echo '</tr>';

            echo '<tr><td colspan="7"></td></tr>'; 
            echo '<tr><td colspan="7" style="font-weight: bold; font-size: 14px;">OFFICIAL INVENTORY REPORT</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td colspan="2">Date Generated:</td>';
            echo '<td colspan="5">' . date('M d, Y h:i A') . '</td>';
            echo '</tr>';

            echo '<tr><td colspan="7"></td></tr>'; 
            
            // COLUMN HEADERS
            echo '<tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">';
            echo '<td>Product Code</td>';
            echo '<td>Item Name</td>';
            echo '<td>Category</td>';
            echo '<td>Current Stock</td>';
            echo '<td>Min Stock</td>';
            echo '<td>Unit</td>';
            echo '<td>Status</td>';
            echo '</tr>';

            // DATA ROWS
            foreach ($items as $item) {
                $status = ($item->quantity <= $item->min_stock) ? 'CRITICAL (Low Stock)' : 'HEALTHY';
                $statusStyle = ($status === 'HEALTHY') ? 'color: green;' : 'color: red; font-weight: bold;';

                echo '<tr>';
                echo '<td>' . $item->product_code . '</td>';
                echo '<td>' . $item->name . '</td>';
                echo '<td>' . ($item->category->name ?? 'N/A') . '</td>';
                echo '<td style="text-align: center;">' . $item->quantity . '</td>';
                echo '<td style="text-align: center;">' . $item->min_stock . '</td>';
                echo '<td>' . ($item->unit->name ?? 'N/A') . '</td>';
                echo '<td style="' . $statusStyle . '">' . $status . '</td>';
                echo '</tr>';
            }

            // FOOTER SIGNATORIES
            echo '<tr><td colspan="7"></td></tr>'; 
            echo '<tr><td colspan="7"></td></tr>'; 
            echo '<tr><td>Prepared By:</td><td colspan="6">____________________</td></tr>';
            echo '<tr><td>Date Signed:</td><td colspan="6">____________________</td></tr>';
            echo '<tr><td colspan="7"></td></tr>'; 
            echo '<tr><td style="vertical-align: bottom; height: 35px;">Noted By:</td><td colspan="6" style="vertical-align: bottom;">____________________</td></tr>';
            echo '<tr><td></td><td colspan="6" style="vertical-align: top;">(School Head / Property Custodian)</td></tr>';

            echo '</table>';
        };

        return response()->stream($callback, 200, $headers);
    }
}