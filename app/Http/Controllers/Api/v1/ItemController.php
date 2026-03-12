<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
public function index(Request $request)
{
    $items = Item::with(['category', 'unit'])->get();

    if ($request->wantsJson()) {
        return response()->json($items);
    }
}

public function show(Request $request, Item $item)
{
    Gate::authorize('view-inventory');

    $item->load(['category', 'unit']);

    if ($request->wantsJson()) {
        return response()->json($item);
    }
}

public function create(Request $request)
{
    Gate::authorize('manage-inventory');

    $categories = Category::select('id', 'name')->get();
    $units = Unit::select('id', 'name')->get();

    if ($request->wantsJson()) {
        return response()->json([
            'categories' => $categories,
            'units' => $units
        ]);
    }
}

public function store(Request $request)
{
    Gate::authorize('manage-inventory');

    $validated = $request->validate([
        'product_code' => 'required|unique:items,product_code',
        'name'         => 'required|string|max:255',
        'quantity'     => 'required|numeric|min:0',
        'min_stock'    => 'required|numeric|min:0',
        'unit_id'      => 'nullable|exists:units,id',
        'category_id'  => 'nullable|exists:categories,id',
        'description'  => 'nullable|string'
    ]);

    $item = Item::create($validated);

    if ($request->wantsJson()) {
        return response()->json([
            'message' => 'Item created successfully.',
            'item' => $item
        ], 201);
    }
    
    return redirect()->route('items.index')->with('message', 'Item created successfully.');
}

public function edit(Request $request, Item $item)
{
    Gate::authorize('manage-inventory');

    $categories = Category::select('id', 'name')->get();
    $units = Unit::select('id', 'name')->get();

    if ($request->wantsJson()) {
        return response()->json([
            'item' => $item,
            'categories' => $categories,
            'units' => $units
        ]);
    }
}

public function update(Request $request, Item $item)
{
    Gate::authorize('manage-inventory');

    $validated = $request->validate([
        'product_code' => 'required|string|max:255|unique:items,product_code,' . $item->id,
        'name'         => 'required|string|max:255',
        'min_stock'    => 'required|numeric|min:0',
        'unit_id'      => 'nullable|exists:units,id',
        'category_id'  => 'nullable|exists:categories,id',
        'description'  => 'nullable|string'
    ]);

    $item->update($validated);

    if ($request->wantsJson()) {
        return response()->json([
            'message' => 'Item updated successfully.',
            'item' => $item
        ]);
    }

    return redirect()->route('items.index')->with('message', 'Item updated successfully.');
}

public function destroy(Request $request, Item $item)
{
    Gate::authorize('delete-inventory');

    $item->delete();

    if ($request->wantsJson()) {
        return response()->json(['message' => 'Item deleted.']);
    }

    return redirect()->route('items.index')->with('message', 'Item deleted.');
}

public function generateProductCode(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
    ]);

    $category = Category::findOrFail($request->category_id);
    $categoryPrefix = strtoupper(str_replace(' ', '', $category->name)); 

    $latestItem = Item::where('category_id', $category->id)
                        ->orderBy('id', 'desc')
                        ->first();

    if (!$latestItem || !$latestItem->product_code) {
        $nextCode = $categoryPrefix . '-001';
    } else {
        $parts = explode('-', $latestItem->product_code);
        $lastNumber = intval(end($parts)); 
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT); 
        $nextCode = $categoryPrefix . '-' . $nextNumber;
    }

    return response()->json(['next_code' => $nextCode]);
}
}
