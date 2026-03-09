<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Department;
use App\Models\Category;
use App\Models\Supplier; // Added for StockIn form
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index()
    {
        $stockIn = StockIn::with(['item.category', 'supplier'])->get()->map(function ($record) {
            return [
                'id' => 'IN-' . str_pad($record->id, 5, '0', STR_PAD_LEFT),
                'raw_id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'received_by' => $record->received_by,
                'released_to' => null,
                'department' => null,
                'note' => "Supplier: " . ($record->supplier->name ?? 'N/A'),
            ];
        });

        $stockOut = StockOut::with(['item.category'])->get()->map(function ($record) {
            return [
                'id' => 'OUT-' . str_pad($record->id, 5, '0', STR_PAD_LEFT),
                'raw_id' => 'out-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'Out',
                'quantity' => $record->quantity,
                'department' => $record->department,
                'released_to' => $record->released_to,
                'released_by' => $record->released_by,
                'note' => $record->purpose ?? 'Release',
            ];
        });

        // Combined Sorting: Precise Timestamp + Numeric ID Tie-breaker for default arrangement
        $mergedTransactions = $stockIn->concat($stockOut)
            ->sort(function ($a, $b) {
                // Primary sort: Date/Time descending
                $dateCompare = $b['created_at'] <=> $a['created_at'];
                if ($dateCompare !== 0) return $dateCompare;

                // Secondary sort: Numeric ID suffix descending (e.g. 00002 > 00001)
                $idA = (int) filter_var($a['id'], FILTER_SANITIZE_NUMBER_INT);
                $idB = (int) filter_var($b['id'], FILTER_SANITIZE_NUMBER_INT);
                return $idB <=> $idA;
            })
            ->values();

        return Inertia::render('Transactions/Index', [
            'transactions' => $mergedTransactions,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function stockIn()
    {
        return Inertia::render('Transactions/StockIn', [
            'items' => Item::orderBy('name')->get(),
            'suppliers' => [] 
        ]);
    }

    public function stockOut()
    {
        return Inertia::render('Transactions/StockOut', [
            'items' => Item::where('quantity', '>', 0)->orderBy('name')->get(), 
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Change validation to string since you don't have a suppliers table
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'received_by' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255', // Changed from exists:suppliers
            'date_received' => 'required|date',
            'unit_cost' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            // 2. Map 'supplier_id' (the text from Vue) to whatever your DB column is
            // If your StockIn model uses 'supplier_name', change the key below:
            StockIn::create([
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'received_by' => $validated['received_by'],
                'supplier_name' => $validated['supplier_id'], // Adjust column name if needed
                'date_received' => $validated['date_received'],
                'unit_cost' => $validated['unit_cost'] ?? 0,
            ]);

            // 3. Use 'quantity' instead of 'stock' to match your items table
            $item = Item::find($validated['item_id']);
            $item->increment('quantity', $validated['quantity']); 
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Stock-in recorded successfully.');
    }

    public function store_bulk_out(Request $request)
    {
        $validated = $request->validate([
            // 1. Match 'line_items' from your Vue form
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
            'department' => 'required|string',
            'released_to' => 'required|string',
            'purpose' => 'nullable|string',
            'date_released' => 'required|date', // Added validation for date
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['line_items'] as $itemData) {
                StockOut::create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'department' => $validated['department'],
                    'released_to' => $validated['released_to'],
                    'purpose' => $validated['purpose'],
                    'date_released' => $validated['date_released'],
                    'released_by' => auth()->user()->name,
                ]);

                // 2. Corrected from 'stock' to 'quantity'
                $item = Item::findOrFail($itemData['item_id']);
                $item->decrement('quantity', $itemData['quantity']);
            }
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Bulk issuance completed and stock levels updated.');
    }

public function exportByDepartment(Request $request)
{
    $request->validate(['department' => 'required|string']);
    $department = $request->department;

    $transactions = StockOut::where('department', $department)
        ->with(['item.category', 'item.unit'])
        ->get()
        // Group by Category Name para magkadikit ang magkakauri
        ->groupBy(function($trx) {
            return $trx->item->category->name ?? 'UNCATEGORIZED';
        })
        ->sortKeys() // Ayusin ang Categories (A-Z)
        ->map(function ($itemsInArchive) {
            // Sa loob ng bawat category, i-group ang same items at i-sort by Product Code
            return $itemsInArchive->groupBy('item_id')->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'product_code' => $first->item->product_code,
                    'item' => $first->item,
                    'total_quantity' => $group->sum('quantity'),
                    'combined_remarks' => $group->map(function($trx) {
                        return ($trx->released_to ?? 'N/A') . ($trx->purpose ? " ({$trx->purpose})" : "");
                    })->unique()->implode(', ')
                ];
            })->sortBy('product_code');
        })
        ->flatten(1); // Gawing isang listahan na lang para sa Blade

    $title = "STOCK ISSUANCE FORM";

    return Pdf::loadView('pdf.bulk_transactions', compact('transactions', 'title', 'department'))
        ->setPaper('letter', 'portrait')
        ->stream("Registry-{$department}.pdf");
}

    public function exportPdf($id)
    {
        if (str_starts_with($id, 'in-')) {
            $realId = str_replace('in-', '', $id);
            $transaction = StockIn::with('item')->findOrFail($realId);
            
            $transaction->type = 'In'; // Manual nating nilalagyan ng type
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'))
            ->setPaper('letter', 'portrait');
            return $pdf->download('Stock-In-Report-' . $realId . '.pdf');
        }

        // 2. Check kung Stock Out (may 'out-' sa unahan)
        if (str_starts_with($id, 'out-')) {
            $realId = str_replace('out-', '', $id);
            $transaction = StockOut::with('item')->findOrFail($realId);
            
            $transaction->type = 'Out'; // Manual nating nilalagyan ng type
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'))
            ->setPaper('letter', 'portrait');
            return $pdf->download('Stock-Out-Report-' . $realId . '.pdf');
        }

        abort(404, 'Transaction not found.');
    }
}