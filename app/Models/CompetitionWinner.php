<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionWinner extends Model
{
    protected $fillable = [
        'competition_id',
        'achieved_value',
        'winner_id'
    ];

    protected $casts = [
        'achieved_value' => 'decimal:2',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function winner()
    {
        return $this->belongsTo(AppUser::class);
    }
}
