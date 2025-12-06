<?php

namespace App\Models;

use App\Models\Uom;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    public function PurchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'purchase_unit');
    }
}
