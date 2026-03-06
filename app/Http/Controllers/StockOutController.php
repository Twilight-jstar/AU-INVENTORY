<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\Item;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    public function store(Request $request)
    {
        // 1. I-validate muna ang request para sigurado tayong may laman ang quantity
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_released' => 'required|date',
        ]);

        $item = Item::findOrFail($request->item_id);

        // 2. CHECK 1: Hindi dapat mag-negative ang stock (Existing logic mo)
        if ($item->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock! Not enough items in the inventory.');
        }

        // 3. CHECK 2: Ang "Hard Stop" para sa Minimum Stock Level
        // Equation: $item->quantity - $request->quantity < $item->min_stock
        $remainingStock = $item->quantity - $request->quantity;

        if ($remainingStock < $item->min_stock) {
            return redirect()->back()->with('error', "Transaction Denied! Releasing {$request->quantity} units will drop the stock below the minimum level of {$item->min_stock}. (Current Stock: {$item->quantity})");
        }

        // 4. Kung pasado sa lahat ng checks, i-record na ang release
        StockOut::create($request->all());

        // 5. Bawasan ang Item quantity
        $item->decrement('quantity', $request->quantity);

        return redirect()->back()->with('success', 'Stock released successfully!');
    }
}