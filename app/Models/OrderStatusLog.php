<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'customer_order_id',
        'status',
        'changed_by_type',
        'changed_by_id',
        'notes'
    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class);
    }
    public function changedBy()
    {
        return $this->morphTo();
    }
}
