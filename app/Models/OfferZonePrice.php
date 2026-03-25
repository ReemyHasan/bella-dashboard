<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferZonePrice extends Model
{
    protected $fillable = [
        'offer_id',
        'zone_id',
        'price',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
