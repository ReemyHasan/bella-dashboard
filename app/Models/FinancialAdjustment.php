<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialAdjustment extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'from_vault_id',
        'to_vault_id',
        'type',
        'amount',
        'reason',
        'notes',
        'status',
        'requested_by_id',
        'requested_by_type',
        'requested_for_id',
        'requested_for_type',
        'reviewed_by',
        'reviewed_at',
        'review_notes'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "reviewed_at_formatted"
    ];

    protected $casts = [
        'amount' => 'decimal:2'

    ];
    public function getReviewedAtFormattedAttribute()
    {
        return $this->reviewed_at
            ? showDateTime($this->reviewed_at)
            : null;
    }


    public function fromVault()
    {
        return $this->belongsTo(Vault::class, 'from_vault_id');
    }
    public function toVault()
    {
        return $this->belongsTo(Vault::class, 'to_vault_id');
    }

    public function requestedBy()
    {
        return $this->morphTo();
    }

    public function requestedFor()
    {
        return $this->morphTo();
    }

    public function reviewedBy()
    {
        return $this->belongsTo(DashUser::class, 'reviewed_by');
    }
}
