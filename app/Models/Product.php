<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantPrice;
use App\Models\productVariant;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];
    public function productVariantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id', 'id');
    }
    public function productVariant()
    {


        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }
    public function scopeByVariant()
    {
    }
}
