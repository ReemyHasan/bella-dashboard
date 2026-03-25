<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressAppUser extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $table = "address_app_user";
    protected $fillable = [
        'address_id',
        'app_user_id',
        'is_main'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
