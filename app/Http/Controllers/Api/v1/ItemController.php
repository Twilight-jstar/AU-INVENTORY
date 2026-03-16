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
        // No gate here allows the Registry to load for all authenticated users.
        $items = Item::with(['category', 'unit'])->get();
        return response()->json($items);
    }

    public function show(Item $item)
    {
        Gate::authorize('view-inventory');
        $item->load(['category', 'unit']);
        return response()->json($item);
    }

    public function create()
    {
        Gate::authorize('manage-inventory');
        return response()->json([
            'categories' => Category::select('id', 'name')->get(),
            'units' => Unit::select('id', 'name')->get()
        ]);
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

        return response()->json([
            'message' => 'Item created successfully.',
            'item' => $item
        ], 201);
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

        return response()->json([
            'message' => 'Item updated successfully.',
            'item' => $item
        ]);
    }

    public function destroy(Item $item)
    {
        // This now matches the definition in AppServiceProvider
        Gate::authorize('delete-inventory');

        $item->delete();
        return response()->json(['message' => 'Item deleted successfully.']);
    }

    public function generateProductCode(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:categories,id']);

        $category = Category::findOrFail($request->category_id);
        $prefix = strtoupper(str_replace(' ', '', $category->name)); 

        $latest = Item::where('category_id', $category->id)->latest('id')->first();

        $nextNumber = $latest ? intval(last(explode('-', $latest->product_code))) + 1 : 1;
        $nextCode = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return response()->json(['next_code' => $nextCode]);
    }
}