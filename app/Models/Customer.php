<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'password',
        'mobile',
        'profile_link',
        'is_blocked',
        'blocked_date',
        'blocked_reason',
        'created_by_id',
        'created_by_type',
        'blocked_by_id',
        'blocked_by_type',
        'updated_by_id',
        'updated_by_type'

    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'password' => 'hashed',

    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "blocked_date_formatted"
    ];
    protected $hidden = [
        'password',
    ];

    public function getBlockedDateFormattedAttribute()
    {
        return $this->blocked_date
            ? showDateTime($this->blocked_date, 'Y-m-d')
            : null;
    }
    // Morph relations
    public function createdBy()
    {
        return $this->morphTo();
    }

    public function blockedBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
    }

    // Addresses
    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'customer_addresses')
            ->withPivot(['extra_details', 'is_main'])
            ->withTimestamps();
    }
}
