<?php

namespace App\Models;

use App\Observers\SubTeamObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([SubTeamObserver::class])]
class SubTeam extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'team_id',
        'name',
        'team_leader_id',
        'active',
        'is_direct'
    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    protected $casts = [
        'active' => 'boolean',
        'is_direct' => 'boolean',

    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function teamLeader()
    {
        return $this->belongsTo(AppUser::class, 'team_leader_id');
    }

    public function users()
    {
        return $this->hasMany(AppUser::class, 'subteam_id');
    }
    public function importantProducts()
    {
        return $this->morphMany(ImportantProduct::class, 'important_for');
    }
}
