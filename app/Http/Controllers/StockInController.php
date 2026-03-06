<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Item;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_received' => 'required|date',
        ]);

        // 1. Record the transaction
        StockIn::create($request->all());

        // 2. Increase the Item quantity
        $item = Item::find($request->item_id);
        $item->increment('quantity', $request->quantity);

        return redirect()->back()->with('success', 'Stock added successfully!');
    }
}