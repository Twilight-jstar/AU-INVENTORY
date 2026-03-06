<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Items/Index', [
            'items' => Item::with(['category', 'unit'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Items/Create', [
            'categories' => Category::all(),
            'units' => Unit::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|unique:items,product_code',
            'name'         => 'required|string|max:255',
            'quantity'     => 'required|numeric|min:0',
            'min_stock'    => 'required|numeric|min:0', // Added this
            'unit_id'      => 'nullable|exists:units,id',
            'category_id'  => 'nullable|exists:categories,id',
            'description'  => 'nullable|string'
        ]);

        Item::create($validated);
        
        return redirect()->route('items.index')->with('message', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        return Inertia::render('Items/Edit', [
            'item' => $item,
            'categories' => Category::all(),
            'units' => Unit::all()
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'product_code' => 'required|unique:items,product_code,' . $item->id,
            'name'         => 'required|string|max:255',
            'min_stock'    => 'required|numeric|min:0',
            'unit_id'      => 'nullable|exists:units,id',
            'category_id'  => 'nullable|exists:categories,id',
            'description'  => 'nullable|string'
        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('message', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('message', 'Item deleted.');
    }
}