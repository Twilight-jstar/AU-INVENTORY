<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Para sa error logging

class StockInController extends Controller
{
    public function store(Request $request)
    {
        // 1. I-validate ang request
        // SIGURADUHIN: Ang 'date_received' sa Vue form ay dapat 'date_received' din dito.
        $request->validate([
            'item_id'       => 'required|exists:items,id',
            'quantity'      => 'required|numeric|min:1',
            'date_received' => 'required|date', 
        ]);

        try {
            DB::transaction(function () use ($request) {
                $item = Item::findOrFail($request->item_id);

                // 2. I-save ang transaction record
                StockIn::create([
                    'item_id'       => $request->item_id,
                    'quantity'      => (int) $request->quantity,
                    'date_received' => $request->date_received,
                ]);

                // 3. I-update ang quantity sa items table
                $item->increment('quantity', (int) $request->quantity);
            });

            // 4. Redirect pabalik sa items index
            return redirect()->route('items.index')->with('success', 'Stock added successfully!');

        } catch (\Exception $e) {
            // Kung may error sa database (halimbawa: missing column), dito babagsak
            Log::error("Stock In Error: " . $e->getMessage());
            
            return redirect()->back()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }
}