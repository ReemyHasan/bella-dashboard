<?php

namespace App\Models;

use App\Observers\OfferObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([OfferObserver::class])]
class Offer extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'symbol',
        'description',
        'summary',
        'marketing_description',
        'active'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"

    ];

    protected $casts = [
        'active' => 'boolean'
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'offer_tags')
            ->withTimestamps();
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'offer_warehouses')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function offerProducts()
    {
        return $this->hasMany(OfferProduct::class);
    }

    public function offerWarehouses()
    {
        return $this->hasMany(OfferWarehouse::class);
    }

    public function zonePrices()
    {
        return $this->hasMany(OfferZonePrice::class);
    }
    public function images()
    {
        return $this->hasMany(OfferImage::class);
    }

     public function mainImage()
    {
        return $this->hasOne(OfferImage::class)->where('is_main', true);
    }
}
