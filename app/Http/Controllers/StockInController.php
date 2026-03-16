<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockInController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id'       => 'required|exists:items,id',
            'quantity'      => 'required|numeric|min:1',
            'date_received' => 'required|date', 
        ]);

        try {
            $record = DB::transaction(function () use ($request) {
                $item = Item::findOrFail($request->item_id);

                $stockIn = StockIn::create([
                    'item_id'       => $request->item_id,
                    'quantity'      => (int) $request->quantity,
                    'date_received' => $request->date_received,
                    // Kapag API/Sanctum gamit, isama natin sino ang naka-login
                    'received_by'   => auth()->user()?->name ?? 'System',
                ]);

                $item->increment('quantity', (int) $request->quantity);
                
                return $stockIn;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Stock added successfully!',
                    'data' => $record
                ], 201);
            }

            return redirect()->route('items.index')->with('success', 'Stock added successfully!');

        } catch (\Exception $e) {
            Log::error("Stock In Error: " . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Database Error: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }
}