<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\ItemTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_items'      => Item::count(),
                'low_stock_count'  => Item::where('quantity', '<=', 5)->count(),
                'total_categories' => Category::count(),
                'recent_updates'   => ItemTransaction::whereDate('created_at', today())->count(),
            ],
            'low_stock_items' => Item::where('quantity', '<=', 5)
                ->with(['unit', 'category'])
                ->limit(5)
                ->get(),
            'recent_transactions' => ItemTransaction::with('item')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
