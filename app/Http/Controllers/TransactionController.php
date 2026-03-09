<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Department;
use App\Models\Category;
use App\Models\Supplier; 
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

        $mergedTransactions = $stockIn->concat($stockOut)
            ->sort(function ($a, $b) {
                $dateCompare = $b['created_at'] <=> $a['created_at'];
                if ($dateCompare !== 0) return $dateCompare;

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
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'received_by' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255', 
            'date_received' => 'required|date',
            'unit_cost' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            StockIn::create([
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'received_by' => $validated['received_by'],
                'supplier_name' => $validated['supplier_id'], 
                'date_received' => $validated['date_received'],
                'unit_cost' => $validated['unit_cost'] ?? 0,
            ]);

            $item = Item::find($validated['item_id']);
            $item->increment('quantity', $validated['quantity']); 
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Stock-in recorded successfully.');
    }

    // ============================================================
    // UPDATED: store_bulk_out with Orange Warning Logic
    // ============================================================
    public function store_bulk_out(Request $request)
    {
        $validated = $request->validate([
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
            'department' => 'required|string',
            'released_to' => 'required|string',
            'purpose' => 'nullable|string',
            'date_released' => 'required|date', 
        ]);

        $lowStockItems = [];

        DB::transaction(function () use ($validated, &$lowStockItems) {
            foreach ($validated['line_items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                StockOut::create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'department' => $validated['department'],
                    'released_to' => $validated['released_to'],
                    'purpose' => $validated['purpose'],
                    'date_released' => $validated['date_released'],
                    'released_by' => auth()->user()->name,
                ]);

                $item->decrement('quantity', $itemData['quantity']);
                
                // Logic for Warning
                $item->refresh();
                $finalQty = (int) $item->quantity;
                $threshold = ($item->min_stock && $item->min_stock > 0) ? (int) $item->min_stock : 10;

                if ($finalQty <= $threshold) {
                    $lowStockItems[] = "{$item->name} ({$finalQty} left)";
                }
            }
        });

        // Kung may tinamaan na threshold, 'warning' (Orange) ang ise-send
        if (count($lowStockItems) > 0) {
            $names = implode(', ', $lowStockItems);
            return redirect()->route('transactions.index')
                ->with('warning', "Issuance successful, but low stock detected for: {$names}");
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Bulk issuance completed and stock levels updated.');
    }

    public function exportByDepartment(Request $request)
    {
        $request->validate(['department' => 'required|string']);
        $department = $request->department;

        $transactions = StockOut::where('department', $department)
            ->with(['item.category'])
            ->get()
            ->map(function ($trx) {
                $trx->type = 'Out'; 
                return $trx;
            });

        $title = "Movement Registry Report: " . $department;
        $type = 'Out';

        return Pdf::loadView('pdf.bulk_transactions', compact('transactions', 'title', 'type', 'department'))
            ->setPaper('a4', 'landscape')
            ->stream("Registry-{$department}.pdf");
    }

    public function exportPdf($id)
    {
        if (str_starts_with($id, 'in-')) {
            $realId = str_replace('in-', '', $id);
            $transaction = StockIn::with('item')->findOrFail($realId);
            
            $transaction->type = 'In'; 
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'))
            ->setPaper('letter', 'portrait');
            return $pdf->download('Stock-In-Report-' . $realId . '.pdf');
        }

        if (str_starts_with($id, 'out-')) {
            $realId = str_replace('out-', '', $id);
            $transaction = StockOut::with('item')->findOrFail($realId);
            
            $transaction->type = 'Out'; 
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'))
            ->setPaper('letter', 'portrait');
            return $pdf->download('Stock-Out-Report-' . $realId . '.pdf');
        }

        abort(404, 'Transaction not found.');
    }
}