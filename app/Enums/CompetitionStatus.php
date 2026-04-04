<?php

namespace App\Enums;

enum CompetitionStatus: string
{
    case draft = 'draft';
    case active = 'active';
    case ended = 'ended';
}
