<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTransaction extends Model
{
    use HasFactory;

    // By default, Laravel looks for "item_transactions" table. 
    // If you named your migration differently, you'd define protected $table here.

    protected $fillable = [
        'items_id',
        'type',
        'quantity',
        'transaction_date'
    ];

    // Every transaction belongs to one Item
    public function item(): BelongsTo
    {
        // We specify 'items_id' because it doesn't follow the 'item_id' default
        return $this->belongsTo(Item::class, 'items_id');
    }
}
