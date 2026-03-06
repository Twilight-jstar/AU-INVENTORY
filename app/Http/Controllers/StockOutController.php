<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\Item;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    public function store(Request $request)
    {
        $item = Item::findOrFail($request->item_id);

        // Validation: Prevent negative stock
        if ($item->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock!');
        }

        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_released' => 'required|date',
        ]);

        // 1. Record the release
        StockOut::create($request->all());

        // 2. Decrease the Item quantity
        $item->decrement('quantity', $request->quantity);

        return redirect()->back()->with('success', 'Stock released successfully!');
    }
}