<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia; // Add this

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
            'product_code' => 'required|unique:items',
            'name'         => 'required',
            'quantity'     => 'required|numeric',
            'unit_id'      => 'nullable|exists:units,id',
            'category_id'  => 'nullable|exists:categories,id',
            'description'  => 'nullable'
        ]);

        Item::create($validated);
        
        return redirect()->route('items.index');
    }
}
