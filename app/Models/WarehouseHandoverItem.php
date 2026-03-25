<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseHandoverItem extends Model
{
    protected $fillable = [
        'handover_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
        'delivered_quantity'
    ];

    public function handover()
    {
        return $this->belongsTo(WarehouseHandover::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
