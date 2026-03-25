<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAssignee extends Model
{
    use HasFactory, HasFormattedTimestamps;

    protected $fillable = [
        'message_id',
        'team_id',
        'sub_team_id',
        'marketer_id',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function subTeam()
    {
        return $this->belongsTo(SubTeam::class);
    }

    public function marketer()
    {
        return $this->belongsTo(AppUser::class);
    }
}
