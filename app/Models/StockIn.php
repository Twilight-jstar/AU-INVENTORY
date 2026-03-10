<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIn extends Model
{
    protected $table = 'stock_in';

    protected $fillable = [
        'item_id', 
        'user_id', 
        'ref_no', 
        'quantity', 
        'unit_cost', 
        'date_received', 
        'supplier_name', 
        'received_by'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The user who recorded this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}