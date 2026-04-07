<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'city_id',
        'warehouse_id',
        'name',
        'symbol',
        'delivery_cost',

    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    protected $casts = [
        'delivery_cost' => 'decimal:2',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
