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
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_released' => 'required|date',
        ]);
        $item = Item::findOrFail($request->item_id);
        
        if ($item->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock! Not enough items in the inventory.');
        }
        $remainingStock = $item->quantity - $request->quantity;
        if ($remainingStock < $item->min_stock) {
            return redirect()->back()->with('error', "Transaction Denied! Releasing {$request->quantity} units will drop the stock below the minimum level of {$item->min_stock}. (Current Stock: {$item->quantity})");
        }
        StockOut::create($request->all());
        $item->decrement('quantity', $request->quantity);
        return redirect()->back()->with('success', 'Stock released successfully!');
    }
}