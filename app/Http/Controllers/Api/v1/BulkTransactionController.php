<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkTransactionController extends Controller
{
    /**
     * Record multiple transactions and update item quantities in bulk.
     */
    public function bulkInOut(Request $request)
    {
        // Validate the array of transactions
        $validated = $request->validate([
            'transactions'                        => 'required|array|min:1',
            'transactions.*.item_id'              => 'required|exists:items,id',
            'transactions.*.user_id'              => 'required|exists:users,id',
            'transactions.*.type'                 => 'required|in:In,Out',
            'transactions.*.quantity'             => 'required|integer|min:1',
            'transactions.*.source_destination'   => 'nullable|string|max:255',
            'transactions.*.personnel_name'       => 'nullable|string|max:255',
            'transactions.*.reference_no'         => 'nullable|string|max:255',
            'transactions.*.note'                 => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Process each transaction
            foreach ($validated['transactions'] as $txnData) {
                
                // Lock the specific item for update to prevent race conditions
                $item = Item::lockForUpdate()->findOrFail($txnData['item_id']);

                // Check for sufficient stock if the type is 'Out'
                if ($txnData['type'] === 'Out' && $item->quantity < $txnData['quantity']) {
                    // Rolling back entirely if ANY item fails the check
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Bulk operation failed. Insufficient stock for Item ID {$item->id}. Current quantity: {$item->quantity}, Requested: {$txnData['quantity']}."
                    ], 422);
                }

                // Create the transaction record
                $transaction = Transaction::create([
                    'item_id'            => $txnData['item_id'],
                    'user_id'            => $txnData['user_id'],
                    'type'               => $txnData['type'],
                    'quantity'           => $txnData['quantity'],
                    'source_destination' => $txnData['source_destination'] ?? null,
                    'personnel_name'     => $txnData['personnel_name'] ?? null,
                    'reference_no'       => $txnData['reference_no'] ?? null,
                    'note'               => $txnData['note'] ?? null,
                ]);

                // Update the quantity
                if ($txnData['type'] === 'In') {
                    $item->increment('quantity', $txnData['quantity']);
                } else {
                    $item->decrement('quantity', $txnData['quantity']);
                }
            }

            // Commit the changes if all items pass
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk transaction recorded successfully.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during processing of transaction.'
            ], 500);
        }
    }
}