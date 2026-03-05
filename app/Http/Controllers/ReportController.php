<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function download()
    {
        // Pangalan ng file kapag na-download
        $fileName = 'School_Inventory_Report_' . date('Y-m-d') . '.csv';
        
        // Kunin ang lahat ng items kasama ang category at unit relationships
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
            
            // --- HEADER NG REPORT ---
            fputcsv($file, ['SCHOOL INVENTORY REPORT']);
            fputcsv($file, ['Date Generated:', date('M d, Y h:i A')]);
            fputcsv($file, []); // Blank line para malinis tignan
            
            // --- MGA PANGALAN NG COLUMN SA EXCEL ---
            fputcsv($file, ['Item Name', 'Category', 'Current Stock', 'Unit', 'Status']);

            // --- ILAGAY ANG MGA GAMIT MULA SA DATABASE ---
            foreach ($items as $item) {
                // Kung 5 o pababa, 'Critical' ang lalabas. Kung mataas, 'Healthy'
                $status = $item->quantity <= 5 ? 'Critical' : 'Healthy';
                
                fputcsv($file, [
                    $item->name,
                    $item->category->name ?? 'N/A', // N/A kung walang category
                    $item->quantity,
                    $item->unit->name ?? 'N/A',     // N/A kung walang unit
                    $status
                ]);
            }

            // --- FOOTER PARA SA MGA PIPIRMA ---
            fputcsv($file, []); // Blank line
            fputcsv($file, []); // Blank line
            fputcsv($file, ['Prepared By:', '____________________']);
            fputcsv($file, ['Designation:', '____________________']);
            fputcsv($file, ['Noted By:', '____________________']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}