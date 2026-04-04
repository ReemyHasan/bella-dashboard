<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionOffer extends Model
{
    protected $fillable = [
        'competition_id',
        'offer_id',
        'target_quantity',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
