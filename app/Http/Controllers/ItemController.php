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
        // Only sending id and name keeps the payload light for the dropdowns
        'categories' => Category::select('id', 'name')->get(),
        'units' => Unit::select('id', 'name')->get()
    ]);
}

public function store(Request $request)
{
    // If you don't see this message on screen after clicking save, 
    // the problem is NOT in this file. It is in your Server/Middleware.
    // dd('Controller reached!', $request->all());

    Gate::authorize('manage-inventory');

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'product_code' => 'required|unique:items,product_code',
        'name'         => 'required|string|max:255',
        'quantity'     => 'required|numeric|min:0',
        'min_stock'    => 'required|numeric|min:0',
        'unit_id'      => 'nullable|exists:units,id',
        'category_id'  => 'nullable|exists:categories,id',
        'description'  => 'nullable|string'
    ]);

    if ($validator->fails()) {
        // This will force the errors to show up instead of redirecting you
        dd('Validation Failed!', $validator->errors()->toArray());
    }

    try {
        Item::create($validator->validated());
        return redirect()->route('items')->with('message', 'Item created successfully.');
    } catch (\Exception $e) {
        // This will show you if the database is missing a column
        dd('Database Error!', $e->getMessage());
    }
}

public function edit(Item $item)
{
    Gate::authorize('manage-inventory');

    return Inertia::render('Items/Edit', [
        'item' => $item,
        // Ensure dropdown data is available for the Edit form
        'categories' => Category::select('id', 'name')->get(),
        'units' => Unit::select('id', 'name')->get()
    ]);
}

public function update(Request $request, Item $item)
{
    Gate::authorize('manage-inventory');

    $validated = $request->validate([
        // Ignores the current item's ID during unique check to prevent false errors
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
    
    // Kukunin ang pangalan ng category, aalisin ang spaces, at gagawing uppercase (e.g. "LAPTOP")
    // Pwede nating kunin lang ang first 3 letters kung gusto mo, pero for now buong name muna
    $categoryPrefix = strtoupper(str_replace(' ', '', $category->name)); 

    // Hahanapin ang pinakahuling item sa category na ito
    $latestItem = Item::where('category_id', $category->id)
                        ->orderBy('id', 'desc')
                        ->first();

    if (!$latestItem || !$latestItem->product_code) {
        // Kung walang nahanap, ito ang unang item: CATEGORY-001
        $nextCode = $categoryPrefix . '-001';
    } else {
        // Kung may nahanap (hal. "LAPTOP-005"), kukunin ang number sa dulo
        $parts = explode('-', $latestItem->product_code);
        $lastNumber = intval(end($parts)); 
        
        // Magdadagdag ng 1, at lalagyan ng leading zeros para maging 3 digits (e.g., "006")
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT); 
        
        $nextCode = $categoryPrefix . '-' . $nextNumber;
    }

    return response()->json(['next_code' => $nextCode]);
}
}