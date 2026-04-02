<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    protected $fillable = [
        'currency_id',
        'name',
        'symbol',

    ];

   

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }


    public function tips()
    {
        return $this->hasMany(Tip::class);
    }
    public function productPrices()
    {
        return $this->hasMany(ProductZonePrice::class, 'zone_id');
    }
}
