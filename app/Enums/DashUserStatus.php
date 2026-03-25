<?php

namespace App\Enums;

enum DashUserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case BANNED = 'BANNED';
    case INACTIVE = 'INACTIVE';
}
