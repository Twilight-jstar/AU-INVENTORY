<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Record a new transaction and update the item's quantity.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming API request including the new reference_no
        $validated = $request->validate([
            'item_id'            => 'required|exists:items,id',
            'user_id'            => 'required|exists:users,id',
            'type'               => 'required|in:In,Out',
            'quantity'           => 'required|integer|min:1', // Change to numeric if you decide to use decimals
            'source_destination' => 'nullable|string|max:255',
            'personnel_name'     => 'nullable|string|max:255',
            'reference_no'       => 'nullable|string|max:255',
            'note'               => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

            if ($validated['type'] === 'Out' && $item->quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Current quantity: {$item->quantity}",
                ], 422); 
            }

            // 2. Create the Transaction record (now including reference_no)
            $transaction = Transaction::create([
                'item_id'            => $validated['item_id'],
                'user_id'            => $validated['user_id'], 
                'type'               => $validated['type'],
                'quantity'           => $validated['quantity'],
                'source_destination' => $validated['source_destination'] ?? null,
                'personnel_name'     => $validated['personnel_name'] ?? null,
                'reference_no'       => $validated['reference_no'] ?? null,
                'note'               => $validated['note'] ?? null,
            ]);

            if ($validated['type'] === 'In') {
                $item->increment('quantity', $validated['quantity']);
            } else {
                $item->decrement('quantity', $validated['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction recorded successfully.',
                'data'    => [
                    'transaction'  => $transaction,
                    'new_quantity' => $item->fresh()->quantity,
                    'is_low_stock' => $item->fresh()->isLowStock()
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the transaction.',
                'error'   => $e->getMessage() 
            ], 500); 
        }
    }
}