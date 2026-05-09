<?php

namespace App\Models;

use App\Observers\WarehouseObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([WarehouseObserver::class])]
class Warehouse extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'zone_id',
        'name',
        'is_main',
        'active',
        'keeper_id'

    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    protected $casts = [
        'active' => 'boolean',
        'is_main' => 'boolean',

    ];
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }


    public function products()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function keeper()
    {
        return $this->belongsTo(AppUser::class, 'keeper_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_warehouses')
            ->withPivot('quantity');
    }
}
