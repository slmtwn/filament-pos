<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = ['product_id', 'order_id', 'subtotal', 'qty'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    protected static function booted()
    {
        static::created(function ($orderdetail) {
            if ($orderdetail->order->status === 'completed') {
                // Additional logic when order status changes to completed
                $product = $orderdetail->product;
                if ($product) {
                    $product->decrement('stock', $orderdetail->qty);
                }
            }
        });
    }
}
