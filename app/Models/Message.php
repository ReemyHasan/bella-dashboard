<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\TargetType;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'description',
        'appears_from',
        'appears_to',
        'target_type',
        'assignment_type'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "appears_from_formatted",
        "appears_to_formatted",

    ];

    protected $casts = [
        'assignment_type' => AssignmentType::class,
        'target_type'     => TargetType::class,
    ];

    public function getAppearsFromFormattedAttribute()
    {
        return $this->appears_from
            ? showDateTime($this->appears_from)
            : null;
    }
    public function getAppearsToFormattedAttribute()
    {
        return $this->appears_to
            ? showDateTime($this->appears_to)
            : null;
    }
    public function assignees()
    {
        return $this->hasMany(MessageAssignee::class);
    }
}
