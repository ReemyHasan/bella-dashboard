<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_quantity'
    ];

    protected $casts = [];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function getAvailableAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }
}
