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
        'min_stock',
        'unit_id',
        'category_id',
        'description'
    ];

    // ============================================================
    // 👇 ITO ANG DINAGDAG KO (VERY IMPORTANT) 👇
    // Sinisiguro nito na laging "Number" ang turing sa stock values
    // ============================================================
    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    /**
     * Helper to check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}