<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function download(): StreamedResponse
    {
        // Filename with timestamp
        $fileName = 'School_Inventory_Report_' . date('Y-m-d_His') . '.csv';
        
        // Eager load relationships to prevent N+1 performance issues
        $items = Item::with(['category', 'unit'])->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($items) {
            $file = fopen('php://output', 'w');
            
            // --- REPORT HEADER ---
            fputcsv($file, ['SCHOOL INVENTORY REPORT']);
            fputcsv($file, ['Date Generated:', date('M d, Y h:i A')]);
            fputcsv($file, []); 
            
            // --- COLUMN HEADERS ---
            fputcsv($file, ['Product Code', 'Item Name', 'Category', 'Current Stock', 'Min Stock', 'Unit', 'Status']);

            // --- DATA ROWS ---
            foreach ($items as $item) {
                // Use the dynamic min_stock from your migration
                $status = ($item->quantity <= $item->min_stock) ? 'CRITICAL (Low Stock)' : 'HEALTHY';
                
                fputcsv($file, [
                    $item->product_code,
                    $item->name,
                    $item->category->name ?? 'N/A',
                    $item->quantity,
                    $item->min_stock,
                    $item->unit->name ?? 'N/A',
                    $status
                ]);
            }

            // --- SIGNATORY FOOTER ---
            fputcsv($file, []); 
            fputcsv($file, []); 
            fputcsv($file, ['Prepared By:', '____________________']);
            fputcsv($file, ['Date Signed:', '____________________']);
            fputcsv($file, ['Noted By:', '____________________', '(School Head / Property Custodian)']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}