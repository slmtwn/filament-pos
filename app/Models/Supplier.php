<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'cp_name',
        'cp_email',
        'cp_phone',
        'is_active',
    ];
}
