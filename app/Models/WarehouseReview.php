<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseReview extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'reviewer_id',
        'reviewed_user_id',
        'rating',
        'comment',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    // 🔹 Reviewer
    public function reviewer()
    {
        return $this->belongsTo(AppUser::class, 'reviewer_id');
    }

    // 🔹 Reviewed (warehouse man)
    public function reviewedUser()
    {
        return $this->belongsTo(AppUser::class, 'reviewed_user_id');
    }
}
