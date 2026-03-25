<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTag extends Model
{
    protected $fillable = [
        'offer_id',
        'tag_id'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"

    ];
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
