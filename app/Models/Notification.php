<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasFormattedTimestamps;

    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'type',
        'title',
        'body',
        'read_at',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "read_at_formatted",
    ];

    
    public function getReadAtFormattedAttribute()
    {
        return $this->read_at
            ? showDateTime($this->read_at)
            : null;
    }
    public function notifiable()
    {
        return $this->morphTo();
    }
}
