<?php

namespace App\Enums;

enum CompetitionTarget: string
{

    case all = 'all';
    case teams = 'teams';
    case subteams = 'subteams';
    case marketers = 'marketers';
}
