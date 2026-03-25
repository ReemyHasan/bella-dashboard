<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;


    protected $fillable = [
        'zone_id',
        'amount'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
