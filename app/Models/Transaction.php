<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'item_id', 'user_id', 'type', 'quantity', 
        'source_destination', 'personnel_name', 'note'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper for your Vue/Blade views
    public function getMovementLabelAttribute()
    {
        return $this->type === 'In' ? 'Source' : 'Department';
    }
}