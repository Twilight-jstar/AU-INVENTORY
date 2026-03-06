<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    // Siguraduhin na tugma ito sa pangalan ng table mo sa database
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
    ];

    /**
     * Isang Supplier ay pwedeng may maraming Stock In transactions.
     */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }
}