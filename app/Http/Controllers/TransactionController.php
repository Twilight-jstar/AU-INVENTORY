<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        // 1. Get and Transform Stock In records
        $stockIn = StockIn::with('item')->get()->map(function ($record) {
            return [
                'id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'note' => "Ref: " . ($record->reference_no ?? 'N/A') . " | Recby: " . ($record->received_by ?? 'Admin'),
            ];
        });

        // 2. Get and Transform Stock Out records
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

        // 3. Merge and Sort
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
            'items' => Item::select('id', 'name', 'product_code', 'quantity')->get()
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
                'released_by' => 'required|string|max:255', // Matches migration
                'department' => 'nullable|string|max:255',
                'purpose' => 'nullable|string|max:255',
                'date_released' => 'required|date', // Matches migration
            ]);
        }

        $validated = $request->validate($rules);

        // Database Transaction for Data Safety
        DB::transaction(function () use ($request, $validated) {
            $item = Item::findOrFail($request->item_id);

            if ($request->type === 'In') {
                StockIn::create($validated);
                $item->increment('quantity', $request->quantity);
            } else {
                // Prevent negative stock
                if ($item->quantity < $request->quantity) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => 'Insufficient stock. Current balance: ' . $item->quantity
                    ]);
                }

                StockOut::create($validated);
                $item->decrement('quantity', $request->quantity);
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Stock movement recorded.');
    }
}