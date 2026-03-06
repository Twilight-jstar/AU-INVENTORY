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