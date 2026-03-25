<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'name_ar',
        'guard_name',
        'is_protected'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function users()
    {
        return $this->morphedByMany(
            DashUser::class,
            'model',
            'model_has_roles'
        );
    }
}
