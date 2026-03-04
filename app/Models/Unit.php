<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Use this to allow saving the name via forms.
     */
    protected $fillable = ['name'];

    /**
     * Get the items associated with this unit.
     * One unit (e.g., 'kg') can belong to many items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}