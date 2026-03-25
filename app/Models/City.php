<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    protected $fillable = [
        'zone_id',
        'name',
        'symbol',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
