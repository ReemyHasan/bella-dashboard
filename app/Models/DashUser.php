<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class DashUser extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasFilters, HasFormattedTimestamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'first_name',
        'last_name',
        'user_name',
        'birth_date',
        'mobile',
        'profile_link',
        'password',
        'app_user_id',
        'balance'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'balance' => 'decimal:2',

        ];
    }
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
    ];

    public function appUser()
    {
        return $this->belongsTo(AppUser::class);
    }
    public function createdUsers()
    {
        return $this->hasMany(AppUser::class, 'created_by_dash_user_id');
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

     public function createdVaultTransfers()
    {
        return $this->morphMany(VaultTransfer::class, 'created_by');
    }
}
