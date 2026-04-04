<?php

namespace App\Models;

use App\Observers\AppUserObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([AppUserObserver::class])]
class AppUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'subteam_id',
        'team_id',
        'is_delivery_man',
        'is_warehouse_man',
        'warehouse_id',
        'first_name',
        'last_name',
        'user_name',
        'password',
        'birth_date',
        'join_date',
        'mobile',
        'profile_link',
        'created_by_app_user_id',
        'created_by_dash_user_id',
        'balance',
        'status'
    ];
    protected $hidden = [
        'password',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "birth_date_formatted",
        "join_date_formatted",
        "creator_type",
        "creator",

    ];

    protected $casts = [
        'is_delivery_man' => 'boolean',
        'is_warehouse_man' => 'boolean',
        'balance' => 'decimal:2',
        'password' => 'hashed',
    ];
    public function getJoinDateFormattedAttribute()
    {
        return $this->join_date
            ? showDateTime($this->join_date, 'Y-m-d')
            : null;
    }

    public function getBirthDateFormattedAttribute()
    {
        return $this->birth_date
            ? showDateTime($this->birth_date, 'Y-m-d')
            : null;
    }
    public function getCreatorTypeAttribute(): ?string
    {
        if ($this->created_by_app_user_id) {
            return 'App User';
        }

        if ($this->created_by_dash_user_id) {
            return 'Dashboard User';
        }

        return null;
    }
    public function getCreatorAttribute()
    {
        if ($this->created_by_app_user_id) {
            return $this->createdByAppUser;
        }

        if ($this->created_by_dash_user_id) {
            return $this->createdByDashUser;
        }

        return null;
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function subTeam()
    {
        return $this->belongsTo(SubTeam::class, 'subteam_id');
    }

    public function managedTeam()
    {
        return $this->hasOne(Team::class, 'manager_id');
    }

    public function ledSubTeam()
    {
        return $this->hasOne(SubTeam::class, 'team_leader_id');
    }

    public function createdByAppUser()
    {
        return $this->belongsTo(AppUser::class, 'created_by_app_user_id');
    }

    public function createdByDashUser()
    {
        return $this->belongsTo(DashUser::class, 'created_by_dash_user_id');
    }

    public function createdUsers()
    {
        return $this->hasMany(AppUser::class, 'created_by_app_user_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_app_user')
            ->withPivot('is_main')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->morphToMany(
            Role::class,
            'model',
            'model_has_roles'
        );
    }

    public function permissions()
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            'model_has_permissions'
        );
    }

    /**
     * Assign role to user
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if (!$role) {
            return;
        }

        $this->roles()->detach($role->id);
    }
    /**
     * Check if user has role
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permissionName)
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }


    public function createdCustomers()
    {
        return $this->morphMany(Customer::class, 'created_by');
    }

    public function blockedCustomers()
    {
        return $this->morphMany(Customer::class, 'blocked_by');
    }

    public function updatedCustomers()
    {
        return $this->morphMany(Customer::class, 'updated_by');
    }

    public function dashUser()
    {
        return $this->hasOne(DashUser::class);
    }

    public function keepWarehouse()
    {
        return $this->hasOne(Warehouse::class, 'keeper_id');
    }

    public function orders()
    {
        return $this->hasMany(CustomerOrder::class, 'app_user_id');
    }
}
