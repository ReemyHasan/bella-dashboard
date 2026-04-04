<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionParticipant extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'competition_id',
        'user_id',
        'score',
        'is_winner'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_winner' => 'boolean'
    ];

    // 🔹 Relations
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}
