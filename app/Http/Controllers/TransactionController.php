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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $stockIn = StockIn::with(['item.category', 'user'])->get()->map(function ($record) {
            return [
                'id' => $record->ref_no,
                'raw_id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'received_by' => $record->received_by,
                'recorded_by' => $record->user->name ?? 'System',
                'released_to' => null,
                'department' => null,
                'note' => "Supplier: " . ($record->supplier_name ?? 'N/A'),
            ];
        });

        $stockOut = StockOut::with(['item.category', 'user'])->get()->map(function ($record) {
            return [
                'id' => $record->ref_no,
                'raw_id' => 'out-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'Out',
                'quantity' => $record->quantity,
                'department' => $record->department,
                'released_to' => $record->released_to,
                'released_by' => $record->released_by,
                'recorded_by' => $record->user->name ?? 'System',
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
        ]);
    }

    public function stockOut()
    {
        return Inertia::render('Transactions/StockOut', [
            'items' => Item::where('quantity', '>', 0)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store_bulk_in(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'received_by' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255', 
            'date_received' => 'required|date',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        DB::transaction(function () use ($request) {
            $refNo = $request->date_received;
            $capturedName = Auth::user()->name;

            foreach ($request->line_items as $item) {
                StockIn::create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'supplier_name' => $request->supplier_name,
                    'date_received' => $request->date_received,
                    'received_by' => $capturedName,
                    'ref_no' => $refNo,
                ]);

                Item::findOrFail($item['item_id'])->increment('quantity', $item['quantity']);
            }
        });

        return back()->with('success', 'Stock In recorded.');
    }

    // ============================================================
    // UPDATED: store_bulk_out with Orange Warning Logic
    // ============================================================
    public function store_bulk_out(Request $request)
    {
        $request->validate([
            'department' => 'required',
            'released_to' => 'required|string',
            'date_released' => 'required|date',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        $lowStockItems = [];

        DB::transaction(function () use ($request, &$lowStockItems) {
            $refNo = $request->date_released;
            $capturedName = Auth::user()->name;

            foreach ($request->line_items as $itemData) {
                // 1. Record the Stock Out
                StockOut::create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'department' => $request->department,
                    'date_released' => $request->date_released,
                    'released_by' => $capturedName,
                    'released_to' => $request->released_to,
                    'purpose' => $request->purpose ?? 'Standard Issuance',
                    'ref_no' => $refNo,
                ]);

                // 2. Update the Inventory
                $item = Item::findOrFail($itemData['item_id']);
                $item->decrement('quantity', $itemData['quantity']);

                // 3. Logic for Low Stock Warning
                $item->refresh();
                $finalQty = $item->quantity;
                // Use item's min_stock if it exists, otherwise default to 10
                $threshold = ($item->min_stock && $item->min_stock > 0) ? $item->min_stock : 10;

                if ($finalQty <= $threshold) {
                    $lowStockItems[] = "{$item->name} ({$finalQty} left)";
                }
            }
        });

        // If any items hit the threshold, return with a warning flash message
        if (count($lowStockItems) > 0) {
            $names = implode(', ', $lowStockItems);
            return back()->with('warning', "Stock Out recorded, but low stock detected for: {$names}");
        }

        return back()->with('success', 'Stock Out recorded.');
    }

    public function exportDailyIn(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;

        $transactions = StockIn::whereDate('date_received', $date)
            ->with(['item.unit'])
            ->get()
            ->map(fn($trx) => (object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_received,
                'product_code' => $trx->item->product_code ?? 'N/A',
                'total_quantity' => $trx->quantity,
                'item' => $trx->item, 
                'received_by' => $trx->received_by,
                'combined_remarks' => "Supplier: " . ($trx->supplier_name ?? 'N/A'),
            ]);

        if ($transactions->isEmpty()) {
            return back()->with('error', 'No stock-in transactions found for this date.');
        }

        return Pdf::loadView('pdf.bulk_transactions', [
            'transactions' => $transactions,
            'title' => "DAILY STOCK IN REPORT",
        ])->setPaper('letter', 'portrait')->stream("Daily-Stock-In-{$date}.pdf");
    }

    public function exportByDepartment(Request $request)
    {
        $request->validate(['department' => 'required|string']);
        $department = $request->department;

        $transactions = StockOut::where('department', $department)
            ->with(['item.unit'])
            ->get()
            ->map(fn($trx) => (object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_released,
                'product_code' => $trx->item->product_code ?? 'N/A',
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'released_by' => $trx->released_by,
                'released_to' => $trx->released_to,
                'combined_remarks' => ($trx->released_to ?? 'N/A') . ($trx->purpose ? " ({$trx->purpose})" : "")
            ])->sortByDesc('date')->values();

        if ($transactions->isEmpty()) {
            return back()->with('error', 'No issuance records found for this department.');
        }

        return Pdf::loadView('pdf.bulk_transactions', [
            'transactions' => $transactions,
            'title' => "STOCK ISSUANCE FORM",
            'department' => $department,
            'received_by_name' => $transactions->first()->released_to ?? null
        ])->setPaper('letter', 'portrait')->stream("Registry-{$department}.pdf");
    }

    public function exportPdf($id)
    {
        if (str_starts_with($id, 'in-')) {
            $realId = str_replace('in-', '', $id);
            $trx = StockIn::with(['item.unit'])->findOrFail($realId);
            $transactions = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_received,
                'product_code' => $trx->item->product_code,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'received_by' => $trx->received_by,
                'combined_remarks' => "Supplier: " . $trx->supplier_name
            ]]);
            return Pdf::loadView('pdf.bulk_transactions', [
                'transactions' => $transactions, 
                'title' => "STOCK-IN SLIP"
            ])->setPaper('letter', 'portrait')->stream("Stock-In-{$realId}.pdf");
        }

        if (str_starts_with($id, 'out-')) {
            $realId = str_replace('out-', '', $id);
            $trx = StockOut::with(['item.unit'])->findOrFail($realId);
            $transactions = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_released,
                'product_code' => $trx->item->product_code,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'released_by' => $trx->released_by,
                'combined_remarks' => $trx->released_to . " (" . $trx->purpose . ")"
            ]]);
            return Pdf::loadView('pdf.bulk_transactions', [
                'transactions' => $transactions, 
                'title' => "STOCK-OUT SLIP",
                'received_by_name' => $trx->released_to
            ])->setPaper('letter', 'portrait')->stream("Stock-Out-{$realId}.pdf");
        }

        abort(404);
    }
}