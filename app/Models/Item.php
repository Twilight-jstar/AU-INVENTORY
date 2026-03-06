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
        'min_stock', // Added this to match your migration
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

    // Relationships for the Stock In and Stock Out tables you created
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    /**
     * Optional: Helper to check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}