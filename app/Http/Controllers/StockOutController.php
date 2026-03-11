<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id'       => 'required|exists:items,id',
            'quantity'      => 'required|numeric|min:1',
            'date_released' => 'required|date',
            'department'    => 'required|string', // Required para sa record
        ]);
        
        try {
            $result = DB::transaction(function () use ($request) {
                $item = Item::findOrFail($request->item_id);
                $outQty = (int) $request->quantity;

                if ((int)$item->quantity < $outQty) {
                    throw new \Exception('Insufficient stock!');
                }
                
                $stockOut = StockOut::create([
                    'item_id'       => $request->item_id,
                    'quantity'      => $outQty,
                    'date_released' => $request->date_released,
                    'department'    => $request->department,
                    'released_to'   => $request->released_to ?? 'N/A',
                    'released_by'   => auth()->user()?->name ?? 'System',
                ]);
                
                $item->decrement('quantity', $outQty);
                $item->refresh();

                return [
                    'record' => $stockOut,
                    'finalQty' => (int) $item->quantity,
                    'threshold' => ($item->min_stock && $item->min_stock > 0) ? (int) $item->min_stock : 10
                ];
            });

            // Logic for warnings/messages
            $isLowStock = $result['finalQty'] <= $result['threshold'];
            $msg = $isLowStock 
                ? "REPLENISH NEEDED! Stock is now only {$result['finalQty']}." 
                : "Stock released successfully!";

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $msg,
                    'is_low_stock' => $isLowStock,
                    'data' => $result['record']
                ], 201);
            }

            $flashKey = $isLowStock ? 'warning' : 'success';
            return redirect()->route('items.index')->with($flashKey, $msg);

        } catch (\Exception $e) {
            Log::error("Stock Out Error: " . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}