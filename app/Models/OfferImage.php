<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferImage extends Model
{
    protected $fillable = [
        'offer_id',
        'path',
        'is_main',
        'sort_order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
