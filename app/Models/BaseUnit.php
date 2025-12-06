<?php

namespace App\Models;

use App\Models\Uom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaseUnit extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    public function uoms(): HasMany
    {
        return $this->hasMany(Uom::class);
    }
}
