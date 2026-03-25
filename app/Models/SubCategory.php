<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'image_path',
        'active',
        'main_category_id'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    protected $casts = [
        'active' => 'boolean',
    ];

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class);
    }
}
