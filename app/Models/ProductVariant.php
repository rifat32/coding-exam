<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    public function variantMain()
    {
        return $this->belongsTo(Variant::class, 'variant_id', 'id');
    }
    public function product()
    {
        return $this->hasMany(Product::class, 'product_id', 'id');
    }
}
