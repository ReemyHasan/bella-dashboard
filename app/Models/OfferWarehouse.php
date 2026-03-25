<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferWarehouse extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'offer_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity'

    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"

    ];
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getAvailableAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }
}
