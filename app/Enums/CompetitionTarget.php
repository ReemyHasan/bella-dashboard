<?php

namespace App\Enums;

enum CompetitionTarget: string
{

    case all = 'all';
    case teams = 'teams';
    case subteams = 'subteams';
    case marketers = 'marketers';
    case all_teams = 'all_teams';
    case all_subteams = 'all_subteams';
}
