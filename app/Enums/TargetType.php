<?php

namespace App\Enums;

enum TargetType: string
{
    case TEAM = 'team';
    case SUB_TEAM = 'sub_team';
    case MARKETER = 'marketer';
}
