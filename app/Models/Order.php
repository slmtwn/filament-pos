<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'date',
        'total_price',
        'discount',
        'discount_amount',
        'status',
        'total_payment',
        'payment_method',
        'payment_status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderdetail(): HasMany
    {
        return $this->HasMany(OrderDetail::class);
    }
}
