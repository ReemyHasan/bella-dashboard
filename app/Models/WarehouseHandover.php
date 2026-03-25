<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseHandover extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'requester_warehouse_id',
        'provider_warehouse_id',
        'status',
        'requested_by',
        'responded_by',
        'approved_at',
        'completed_at',
        'notes'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "approved_at_formatted",
        "completed_at_formatted",

    ];
    protected $casts = [
        'active' => 'boolean',
        'is_main' => 'boolean',

    ];
    public function getApprovedAtFormattedAttribute()
    {
        return $this->approved_at
            ? showDateTime($this->approved_at)
            : null;
    }
    public function getCompletedAtFormattedAttribute()
    {
        return $this->completed_at
            ? showDateTime($this->completed_at)
            : null;
    }
    public function items()
    {
        return $this->hasMany(WarehouseHandoverItem::class, 'handover_id');
    }

    public function requesterWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'requester_warehouse_id');
    }

    public function providerWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'provider_warehouse_id');
    }

    public function requester()
    {
        return $this->belongsTo(DashUser::class, 'requested_by');
    }

    public function responder()
    {
        return $this->belongsTo(DashUser::class, 'responded_by');
    }
}
