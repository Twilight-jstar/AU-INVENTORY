<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIn extends Model
{
    use HasFactory;

    // This is required because of the underscore in your table name
    protected $table = 'stock_in';

    protected $fillable = [
        'item_id',
        'supplier_id',
        'quantity',
        'unit_cost',
        'date_received',
        'reference_no',
        'received_by'
    ];

    /**
     * Link back to the Item being restocked
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}