<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'image',
        'brand_id',
        'category_id',
        'sub_category_id',
        'is_active',
        'in_stock',
        'sku',
        'barcode',
        'description',
        'base_price',
        'uom_id',
        'base_unit',
        'purchase_unit',
        'conversion_factor',
        'gross_margin',
    ];

    public function orderdetail(): HasMany
    {
        return $this->HasMany(OrderDetail::class);
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function sub_category(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }
}
