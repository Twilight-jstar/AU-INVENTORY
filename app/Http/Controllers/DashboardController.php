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
    public function index(Request $request)
    {
        // 1. Fetch recent Stock In movements
        $stockIn = StockIn::with('item')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => $item->setAttribute('type', 'In'));

        // 2. Fetch recent Stock Out movements
        $stockOut = StockOut::with('item')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => $item->setAttribute('type', 'Out'));

        // 3. Merge and sort
        $recent_transactions = $stockIn->concat($stockOut)
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        // 4. Prepare Data
        $data = [
            'stats' => [
                'total_items'      => Item::count(),
                'low_stock_count'  => Item::whereRaw('quantity <= min_stock')->count(),
                'total_categories' => Category::count(),
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
        ];

        // API Response
        if ($request->wantsJson()) {
            return response()->json($data);
        }

        // Inertia Response
        return Inertia::render('Dashboard', $data);
    }
}