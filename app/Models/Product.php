<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'main_category_id',
        'sub_category_id',
        'name',
        'slug',
        'size',
        'description',
        'how_to_use',
        'precautions',
        'country_of_origin',
        'active',
        'adjustment_type',
        'adjustment_value',
        'adjustment_operation',
        'brand_id',

    ];

    protected $casts = [
        'active' => 'boolean',
        'adjustment_value' => 'decimal:2',
    ];


    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class);
    }
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function zonePrices()
    {
        return $this->hasMany(ProductZonePrice::class);
    }
    public function warehouseProducts()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_products')
            ->withPivot('quantity');
    }

    public function importantScopes()
    {
        return $this->hasMany(ImportantProduct::class);
    }
}
