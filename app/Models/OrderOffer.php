<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOffer extends Model
{
    protected $fillable = [
        'customer_order_id',
        'offer_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
