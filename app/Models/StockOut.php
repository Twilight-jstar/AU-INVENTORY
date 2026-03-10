<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOut extends Model
{
    protected $table = 'stock_out';

    protected $fillable = [
        'item_id', 
        'user_id', // Added for auditing
        'ref_no', 
        'quantity', 
        'date_released', 
        'released_to', 
        'department', 
        'purpose', 
        'released_by'
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