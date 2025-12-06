<?php

namespace App\Models;

use App\Models\Product;
use App\Models\BaseUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Uom extends Model
{
    protected $fillable = [
        'code',
        'name',
        'base_unit_id',
        'symbol',
        'description',
        'is_active',
    ];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(BaseUnit::class);
    }
    public function product(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
