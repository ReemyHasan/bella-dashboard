<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
     use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

}
