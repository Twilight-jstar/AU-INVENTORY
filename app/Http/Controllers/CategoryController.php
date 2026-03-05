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
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories']);
        Category::create($request->all());
        
        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        return redirect()->route('categories.index');
    }
}