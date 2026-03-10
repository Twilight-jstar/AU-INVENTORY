<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIn extends Model
{
    protected $table = 'stock_in';

    protected $fillable = [
        'item_id', 
        'quantity', 
        'supplier_name', 
        'date_received', 
        'received_by', 
        'ref_no', 
        'unit_cost'
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