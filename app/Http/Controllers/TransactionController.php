<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Department;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $stockIn = StockIn::with(['item.category'])->get()->map(function ($record) {
            return [
                'id' => $record->ref_no,
                'raw_id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'received_by' => $record->received_by,
                'recorded_by' => $record->received_by,
                'released_to' => null,
                'department' => null,
                'note' => "Supplier: " . ($record->supplier_name ?? 'N/A'),
            ];
        });

        $stockOut = StockOut::with(['item.category'])->get()->map(function ($record) {
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
                'recorded_by' => $record->released_by,
                'note' => $record->purpose ?? 'Release',
            ];
        });

        $mergedTransactions = $stockIn->concat($stockOut)
            ->sort(fn($a, $b) => $b['created_at'] <=> $a['created_at'])
            ->values();

        if ($request->wantsJson()) {
            return response()->json([
                'transactions' => $mergedTransactions,
                'departments' => Department::where('is_active', true)->orderBy('name')->get(),
                'categories' => Category::orderBy('name')->get(),
            ]);
        }

        return Inertia::render('Transactions/Index', [
            'transactions' => $mergedTransactions,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function stockIn(Request $request)
    {
        $items = Item::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json(['items' => $items]);
        }

        return Inertia::render('Transactions/StockIn', [
            'items' => $items,
        ]);
    }

    public function stockOut(Request $request)
    {
        $items = Item::where('quantity', '>', 0)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'items' => $items,
                'departments' => $departments
            ]);
        }

        return Inertia::render('Transactions/StockOut', [
            'items' => $items,
            'departments' => $departments,
        ]);
    }

    public function store_bulk_in(Request $request)
    {
        $request->validate([
            'date_received' => 'required|date',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        $lastId = null;

        DB::transaction(function () use ($request, &$lastId) {
            foreach ($request->line_items as $item) {
                $record = StockIn::create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'supplier_name' => $request->supplier_name,
                    'date_received' => $request->date_received,
                    'received_by' => Auth::user()->name,
                    'ref_no' => $request->date_received,
                ]);
                $lastId = $record->id;
                Item::findOrFail($item['item_id'])->increment('quantity', $item['quantity']);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Stock In recorded.',
                'export_id' => 'in-' . $lastId
            ], 201);
        }

        return back()->with([
            'success' => 'Stock In recorded.',
            'export_id' => 'in-' . $lastId
        ]);
    }

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
        $lastId = null;

        DB::transaction(function () use ($request, &$lowStockItems, &$lastId) {
            foreach ($request->line_items as $itemData) {
                $record = StockOut::create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'department' => $request->department,
                    'date_released' => $request->date_released,
                    'released_by' => Auth::user()->name,
                    'released_to' => $request->released_to,
                    'purpose' => $request->purpose ?? 'Standard Issuance',
                    'ref_no' => $request->date_released,
                ]);
                $lastId = $record->id;

                $item = Item::findOrFail($itemData['item_id']);
                $item->decrement('quantity', $itemData['quantity']);
                $item->refresh();
                
                $threshold = ($item->min_stock && $item->min_stock > 0) ? $item->min_stock : 10;
                if ($item->quantity <= $threshold) {
                    $lowStockItems[] = "{$item->name} ({$item->quantity} left)";
                }
            }
        });

        $payload = [
            'status' => 'success',
            'message' => 'Stock Out recorded.', 
            'export_id' => 'out-' . $lastId
        ];

        if (count($lowStockItems) > 0) {
            $payload['warning'] = "Low stock detected: " . implode(', ', $lowStockItems);
        }

        if ($request->wantsJson()) {
            return response()->json($payload, 201);
        }

        return back()->with($payload);
    }

    public function exportDailyIn(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        
        $transactions = StockIn::whereDate('date_received', $date)
            ->with(['item.unit'])
            ->get()
            ->map(function($trx) {
                return (object)[
                    'ref_no' => $trx->ref_no,
                    'date' => $trx->date_received,
                    'total_quantity' => $trx->quantity,
                    'item' => $trx->item,
                    'combined_remarks' => "Supplier: " . ($trx->supplier_name ?? 'N/A'),
                    'received_by' => $trx->received_by
                ];
            });

        if ($transactions->isEmpty()) {
            return $request->wantsJson() 
                ? response()->json(['error' => 'No records found.'], 404) 
                : back()->with('error', 'No records found.');
        }

        return Pdf::loadView('pdf.bulk_transactions', [
            'transactions' => $transactions,
            'title' => "DAILY STOCK IN REPORT",
        ])->setPaper('letter', 'portrait')->stream("Daily-Stock-In-{$date}.pdf");
    }

    public function exportByDepartment(Request $request)
    {
        $request->validate(['department' => 'required|string']);
        
        $transactions = StockOut::where('department', $request->department)
            ->with(['item.unit'])
            ->get()
            ->map(function($trx) {
                return (object)[
                    'ref_no' => $trx->ref_no,
                    'date' => $trx->date_released,
                    'total_quantity' => $trx->quantity, 
                    'item' => $trx->item,
                    'combined_remarks' => ($trx->purpose ? " ({$trx->purpose})" : ""),
                    'released_by' => $trx->released_by,
                    'released_to' => $trx->released_to
                ];
            });

        if ($transactions->isEmpty()) {
            return $request->wantsJson() 
                ? response()->json(['error' => 'No records found.'], 404) 
                : back()->with('error', 'No records found.');
        }

        $lastTrx = $transactions->last();

        return Pdf::loadView('pdf.bulk_transactions', [
            'transactions' => $transactions,
            'title' => "STOCK ISSUANCE FORM",
            'received_by_name' => $lastTrx->released_to,
            'received_by_date' => $lastTrx->date
        ])->setPaper('letter', 'portrait')->stream("Registry-{$request->department}.pdf");
    }

    public function exportPdf(Request $request, $id)
    {
        if (str_starts_with($id, 'in-')) {
            $realId = str_replace('in-', '', $id);
            $trx = StockIn::with(['item.unit'])->findOrFail($realId);
            
            $data = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_received,
                'product_code' => $trx->item->product_code,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'received_by' => $trx->received_by,
                'combined_remarks' => "Supplier: " . ($trx->supplier_name ?? 'N/A')
            ]]);
            
            return Pdf::loadView('pdf.bulk_transactions', [
                'transactions' => $data, 
                'title' => "STOCK-IN SLIP"
            ])->setPaper('letter', 'portrait')->stream("Stock-In-{$realId}.pdf");
        }

        if (str_starts_with($id, 'out-')) {
            $realId = str_replace('out-', '', $id);
            $trx = StockOut::with(['item.unit'])->findOrFail($realId);
            
            $data = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_released,
                'product_code' => $trx->item->product_code,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'released_by' => $trx->released_by,
                'released_to' => $trx->released_to,
                'combined_remarks' => "Purpose: " . ($trx->purpose ?? "Standard Issuance")
            ]]);
            
            return Pdf::loadView('pdf.bulk_transactions', [
                'transactions' => $data, 
                'title' => "STOCK ISSUANCE FORM",
                'received_by_name' => $trx->released_to,
                'received_by_date' => $trx->date_released
            ])->setPaper('letter', 'portrait')->stream("Stock-Out-{$realId}.pdf");
        }

        abort(404);
    }
}