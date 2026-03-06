<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\StockIn;
use App\Models\StockOut;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
public function index()
{
    // 1. Fetch recent Stock In movements (with 'In' type tag)
    $stockIn = StockIn::with('item')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn($item) => $item->setAttribute('type', 'In'));

    // 2. Fetch recent Stock Out movements (with 'Out' type tag)
    $stockOut = StockOut::with('item')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn($item) => $item->setAttribute('type', 'Out'));

    // 3. Merge and sort by creation date
    $recent_transactions = $stockIn->concat($stockOut)
        ->sortByDesc('created_at')
        ->take(8)
        ->values();

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_items'      => Item::count(),
            // Uses the dynamic min_stock column we added earlier
            'low_stock_count'  => Item::whereRaw('quantity <= min_stock')->count(),
            'total_categories' => Category::count(),
            // Combined updates from both tables for today
            'recent_updates'   => StockIn::whereDate('created_at', today())->count() + 
                                    StockOut::whereDate('created_at', today())->count(),
        ],
        'top_stock_items' => Item::orderBy('quantity', 'desc')
            ->limit(5)
            ->get(),
        'low_stock_items' => Item::whereRaw('quantity <= min_stock')
            ->with(['unit', 'category'])
            ->get(),
        'recent_transactions' => $recent_transactions,
    ]);
}
}