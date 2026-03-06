<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index()
    {
        // 1. Get and Transform Stock In records
        $stockIn = StockIn::with(['item', 'supplier'])->get()->map(function ($record) {
            return [
                'id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'unit_cost' => $record->unit_cost,
                'supplier_id' => $record->supplier_id, 
                'note' => "Supplier: " . ($record->supplier->name ?? $record->supplier_id ?? 'N/A') . " | Ref: " . ($record->reference_no ?? 'N/A') . " | Cost: ₱" . number_format($record->unit_cost ?? 0, 2),
            ];
        });

        // 2. Kunin at i-transform ang Stock Out records
        $stockOut = StockOut::with('item')->get()->map(function ($record) {
            $dept = $record->department ? " [{$record->department}]" : "";
            return [
                'id' => 'out-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'Out',
                'quantity' => $record->quantity,
                'note' => ($record->purpose ?? 'Release') . " | To: " . ($record->released_to ?? 'N/A') . $dept,
            ];
        });

        // 3. I-merge at i-sort
        $mergedTransactions = $stockIn->concat($stockOut)
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Transactions/Index', [
            'transactions' => $mergedTransactions
        ]);
    }

    public function create()
{
    return Inertia::render('Transactions/Create', [
        // Kinukuha natin ang mga items pati ang kanilang min_stock
        'items' => Item::select('id', 'name', 'product_code', 'quantity', 'min_stock')->get(),
        
        // DITO NATIN IDINAGDAG: Kinukuha ang pangalan ng user na naka-login
        'auth_name' => auth()->user()->name 
    ]);
}

    public function store(Request $request)
    {
        // Validation Rules
        $rules = [
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:In,Out',
            'quantity' => 'required|numeric|min:0.1',
        ];

        if ($request->type === 'In') {
            $rules = array_merge($rules, [
                'received_by' => 'required|string|max:255',
                'reference_no' => 'nullable|string|max:255',
                'unit_cost' => 'nullable|numeric|min:0',
                'date_received' => 'required|date',
            ]);
        } else {
            $rules = array_merge($rules, [
                'released_to' => 'required|string|max:255',
                'released_by' => 'required|string|max:255',
                'department' => 'nullable|string|max:255',
                'purpose' => 'nullable|string|max:255',
                'date_released' => 'required|date',
            ]);
        }

        $validated = $request->validate($rules);

        // Database Transaction para sa Data Safety
        return DB::transaction(function () use ($request, $validated) {
            $item = Item::findOrFail($request->item_id);

            if ($request->type === 'In') {
                StockIn::create($validated);
                $item->increment('quantity', $request->quantity);
            } else {
                // 1. I-compute ang matitirang stock
                $remainingStock = $item->quantity - $request->quantity;

                // 2. HARD STOP CHECK: Minimum Stock Level
                // Kung bababa sa min_stock, harangin ang transaction
                if ($remainingStock < $item->min_stock) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => "Transaction Denied! Releasing {$request->quantity} units will drop the stock below the minimum level of {$item->min_stock}. (Current Balance: {$item->quantity})"
                    ]);
                }

                // 3. Kung pasado, i-record ang Stock Out at bawasan ang quantity
                StockOut::create($validated);
                $item->decrement('quantity', $request->quantity);
            }

            return redirect()->route('transactions.index')->with('success', 'Stock movement recorded.');
        });
    }

    public function exportPdf($id)
    {
        // 1. Check kung Stock In (may 'in-' sa unahan)
        if (str_starts_with($id, 'in-')) {
            $realId = str_replace('in-', '', $id);
            $transaction = StockIn::with('item')->findOrFail($realId);
            
            $transaction->type = 'In'; // Manual nating nilalagyan ng type
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'));
            return $pdf->download('Stock-In-Report-' . $realId . '.pdf');
        }

        // 2. Check kung Stock Out (may 'out-' sa unahan)
        if (str_starts_with($id, 'out-')) {
            $realId = str_replace('out-', '', $id);
            $transaction = StockOut::with('item')->findOrFail($realId);
            
            $transaction->type = 'Out'; // Manual nating nilalagyan ng type
            $pdf = Pdf::loadView('pdf.transaction', compact('transaction'));
            return $pdf->download('Stock-Out-Report-' . $realId . '.pdf');
        }

        abort(404, 'Transaction not found.');
    }
}