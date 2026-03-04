<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => ItemTransaction::with('item')->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Transactions/Create', [
            'items' => Item::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items_id' => 'required|exists:items,id',
            'type'     => 'required|in:In,Out',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        ItemTransaction::create($validated);

        // Update Item Stock
        $item = Item::find($request->items_id);
        if ($request->type === 'In') {
            $item->increment('quantity', $request->quantity);
        } else {
            $item->decrement('quantity', $request->quantity);
        }

        return redirect()->route('transactions.index');
    }
}
