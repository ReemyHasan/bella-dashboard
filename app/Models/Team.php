<?php

namespace App\Models;

use App\Observers\TeamObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([TeamObserver::class])]
class Team extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'manager_id',
        'active',
        'marketer_percentage',
        'team_leader_percentage',
        'manager_percentage',
        'direct_manager_percentage',
        // 'warehouse_man_percentage',
    ];

    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    protected $casts = [
        'active' => 'boolean',
        'marketer_percentage' => 'decimal:2',
        'team_leader_percentage' => 'decimal:2',
        'manager_percentage' => 'decimal:2',
        'direct_manager_percentage' => 'decimal:2',
        // 'delivery_man_percentage' => 'decimal:2',
        // 'warehouse_man_percentage' => 'decimal:2',
    ];

    public function manager()
    {
        return $this->belongsTo(AppUser::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(AppUser::class);
    }

    public function subTeams()
    {
        return $this->hasMany(SubTeam::class);
    }

    public function directSubTeams()
    {
        return $this->hasMany(SubTeam::class)->where('is_direct', true);
    }

    public function normalSubTeams()
    {
        return $this->hasMany(SubTeam::class)->where('is_direct', false);
    }
    public function importantProducts()
    {
        return $this->morphMany(ImportantProduct::class, 'important_for');
    }
}
