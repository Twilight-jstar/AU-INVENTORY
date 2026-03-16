<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('items')->get();

        if ($request->wantsJson()) {
            return response()->json($categories);
        }

        return Inertia::render('Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100|unique:categories,name'
        ]);

        $category = Category::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category created successfully.',
                'category' => $category
            ], 201);
        }

        return redirect()->route('categories.index')->with('message', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:100|unique:categories,name,' . $category->id
        ]);

        $category->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category updated.',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('message', 'Category updated.');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->items()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot delete category. There are still items assigned to it.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete category. There are still items assigned to it.');
        }

        $category->delete();
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Category deleted.']);
        }

        return redirect()->route('categories.index')->with('message', 'Category deleted.');
    }
}