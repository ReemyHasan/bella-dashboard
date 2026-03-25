<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'delivery_man_id',
        'alter_delivery_man_id',
        'region_id',
        'name',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function deliveryMan()
    {
        return $this->belongsTo(AppUser::class);
    }
    public function alterDeliveryMan()
    {
        return $this->belongsTo(AppUser::class);
    }
    public function appUsers()
    {
        return $this->belongsToMany(AppUser::class, 'address_app_user')
            ->withPivot('is_main')
            ->withTimestamps();
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_addresses')
            ->withPivot(['extra_details', 'is_main'])
            ->withTimestamps();
    }
}
