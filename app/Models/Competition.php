<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'description',
        'start_at',
        'end_at',
        'prize',
        'type',
        'target_value',
        'target',
        'status',
        'co_created_by_id',
        'co_created_by_type',
        'created_by_id',
        'created_by_type',
    ];

    protected $casts = [
        'prize' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        'start_at_formatted',
        'end_at_formatted',
    ];


    public function getStartAtFormattedAttribute()
    {
        return $this->start_at
            ? showDateTime($this->start_at)
            : null;
    }

    public function getEndAtFormattedAttribute()
    {
        return $this->end_at
            ? showDateTime($this->end_at)
            : null;
    }
    /*
    |--------------------------------------------------------------------------
    | Morph Relations
    |--------------------------------------------------------------------------
    */

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function coCreatedBy()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Zones
    |--------------------------------------------------------------------------
    */

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'competition_zone');
    }

    /*
    |--------------------------------------------------------------------------
    | Targets (Products / Offers)
    |--------------------------------------------------------------------------
    */

    public function products()
    {
        return $this->belongsToMany(Product::class, 'competition_products')
            ->withPivot('target_quantity');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'competition_offers')
            ->withPivot('target_quantity');
    }



    public function teams()
    {
        return $this->belongsToMany(Team::class, 'competition_teams');
    }

    public function subteams()
    {
        return $this->belongsToMany(SubTeam::class, 'competition_sub_teams');
    }

    public function marketers()
    {
        return $this->belongsToMany(
            AppUser::class,
            'competition_marketers',
            'competition_id',
            'marketer_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Winners
    |--------------------------------------------------------------------------
    */

    public function winners()
    {
        return $this->hasMany(CompetitionWinner::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers 🔥
    |--------------------------------------------------------------------------
    */

    public function isAll()
    {
        return $this->target === 'all';
    }

    public function isTeams()
    {
        return $this->target === 'teams';
    }

    public function isSubteams()
    {
        return $this->target === 'subteams';
    }

    public function isMarketers()
    {
        return $this->target === 'marketers';
    }

    /**
     * Get the active target relation dynamically
     */
    public function targetRelation()
    {
        return match ($this->target) {
            'teams' => $this->teams(),
            'subteams' => $this->subteams(),
            'marketers' => $this->marketers(),
            default => null,
        };
    }
    public function participants()
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function leaderboard()
    {
        return $this->participants()
            ->with('user')
            ->orderByDesc('score');
    }
}
