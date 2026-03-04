<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'name',
        'quantity',
        'unit_id',
        'category_id',
        'description'
    ];

    // An item belongs to one Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // An item belongs to one Unit
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // An item has many transactions (history)
    public function transactions(): HasMany
    {
        return $this->hasMany(ItemTransaction::class, 'items_id');
    }
}
