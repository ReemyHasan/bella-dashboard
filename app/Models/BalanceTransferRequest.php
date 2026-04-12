<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransferRequest extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'amount',
        'status',
        'notes',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "reviewed_at_formatted"
    ];

    protected $casts = [
        'amount' => 'decimal:2',

    ];
    public function getReviewedAtFormattedAttribute()
    {
        return $this->reviewed_at
            ? showDateTime($this->reviewed_at)
            : null;
    }

    public function fromUser()
    {
        return $this->belongsTo(AppUser::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(AppUser::class, 'to_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(DashUser::class, 'reviewed_by');
    }
}
