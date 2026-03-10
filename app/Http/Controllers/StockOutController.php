<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\Item;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id'       => 'required|exists:items,id',
            'quantity'      => 'required|numeric|min:1',
            'date_released' => 'required|date',
        ]);
        
        $item = Item::findOrFail($request->item_id);
        $outQty = (int) $request->quantity;

        if ((int)$item->quantity < $outQty) {
            return redirect()->back()->with('error', 'Insufficient stock!');
        }
        
        // Save the transaction
        StockOut::create($request->all());
        
        // Update the item quantity
        $item->decrement('quantity', $outQty);
        
        // Kunin ang updated data
        $item->refresh();
        $finalQty = (int) $item->quantity;

        // FORCE CHECK: 
        // Kahit anong mangyari, kung 0 o 1 ang stock, FORCE nating gawing 'warning'
        // para lumitaw ang Orange indicator sa demo mo.
        $threshold = ($item->min_stock && $item->min_stock > 0) ? (int) $item->min_stock : 10;

        if ($finalQty <= $threshold) {
            // Siguraduhin na 'warning' ang key na ginagamit natin dito
            return redirect()->route('items.index')->with('warning', "REPLENISH NEEDED! Stock is now only {$finalQty}.");
        }
        
        return redirect()->route('items.index')->with('success', 'Stock released successfully!');
    }
}