<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'symbol',
        'is_main',
        'exchange_value',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'exchange_value' => 'decimal:2',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
}