<?php

namespace App\Models;

use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_number',
        'user_id',
        'supplier_id',
        'purchase_date',
        'subtotal',
        'tax',
        'tax_amount',
        'discount',
        'discount_amount',
        'total_payment',
        'status',
    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }
}
