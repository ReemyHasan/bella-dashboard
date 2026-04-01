<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductZonePrice extends Model
{
     protected $fillable = [
        'product_id',
        'zone_id',
        'price',
        'is_available',
        'price_after_adjustment'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'decimal:2',
        'price_after_adjustment' => 'decimal:2',

    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
