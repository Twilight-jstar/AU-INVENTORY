<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Categories/Index', [
            // withCount lets the frontend show how many items are in each category
            'categories' => Category::withCount('items')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100|unique:categories,name'
        ]);

        Category::create($validated);
        
        return redirect()->route('categories.index')->with('message', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            // Ignore current category ID during unique check so you can save without changing the name
            'name' => 'required|max:100|unique:categories,name,' . $category->id
        ]);

        $category->update($validated);
        
        return redirect()->route('categories.index')->with('message', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        // Safety Check: Check if items exist in this category before deleting
        if ($category->items()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category. There are still items assigned to it.');
        }

        $category->delete();
        
        return redirect()->route('categories.index')->with('message', 'Category deleted.');
    }
}