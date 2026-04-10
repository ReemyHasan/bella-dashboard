<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUserRequest extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;


    protected $fillable = [
        'app_user_id',
        'user_request_type_id',
        'content',
        'notes',
        'read_at',
        'handled_at',
        'status',
        'requested_by_id',
        'requested_by_type',
        'reviewed_by',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "read_at_formatted",
        "handled_at_formatted"
    ];

    public function getReadAtFormattedAttribute()
    {
        return $this->read_at
            ? showDateTime($this->read_at)
            : null;
    }

    public function getHandledAtFormattedAttribute()
    {
        return $this->handled_at
            ? showDateTime($this->handled_at)
            : null;
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
    public function userRequestType()
    {
        return $this->belongsTo(UserRequestType::class, 'user_request_type_id');
    }

    public function requestedBy()
    {
        return $this->morphTo();
    }
    public function reviewedBy()
    {
        return $this->belongsTo(DashUser::class, 'reviewed_by');
    }
}
