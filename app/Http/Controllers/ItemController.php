<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ItemController extends Controller
{
public function index()
{
    return Inertia::render('Items/Index', [
        'items' => Item::with(['category', 'unit'])->get()
    ]);
}

public function show(Item $item)
{
    Gate::authorize('view-inventory');

    return Inertia::render('Items/Show', [
        'item' => $item->load(['category', 'unit'])
    ]);
}

public function create()
{
    Gate::authorize('manage-inventory');

    return Inertia::render('Items/Create', [
        'categories' => Category::select('id', 'name')->get(),
        'units' => Unit::select('id', 'name')->get()
    ]);
}

public function store(Request $request)
{
    // This will stop the redirect and show you EXACTLY what is being sent
    // and if there are any validation errors.
    $validator = \Validator::make($request->all(), [
        'product_code' => 'required|unique:items,product_code',
        'name'         => 'required|string|max:255',
        'quantity'     => 'required|numeric|min:0',
        'min_stock'    => 'required|numeric|min:0',
        'unit_id'      => 'nullable|exists:units,id',
        'category_id'  => 'nullable|exists:categories,id',
        'description'  => 'nullable|string'
    ]);

    if ($validator->fails()) {
        // This will kill the request and print the errors to your browser screen
        dd('Validation Failed!', $validator->errors()->toArray(), 'Request Data:', $request->all());
    }

    Item::create($validator->validated());
    
    return redirect()->route('items');
}

public function edit(Item $item)
{
    Gate::authorize('manage-inventory');

    return Inertia::render('Items/Edit', [
        'item' => $item,
        'categories' => Category::select('id', 'name')->get(),
        'units' => Unit::select('id', 'name')->get()
    ]);
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

    return redirect()->route('items')->with('message', 'Item updated successfully.');
}

public function destroy(Item $item)
{
    Gate::authorize('delete-inventory');

    $item->delete();
    return redirect()->route('items')->with('message', 'Item deleted.');
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