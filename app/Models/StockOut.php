<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOut extends Model
{
    use HasFactory;

    // Required for the underscore table name
    protected $table = 'stock_out';

    protected $fillable = [
        'item_id',
        'quantity',
        'date_released',
        'released_to',
        'department',
        'purpose',
        'released_by'
    ];

    /**
     * Link back to the Item being removed
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}