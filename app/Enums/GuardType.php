<?php

namespace App\Enums;

enum GuardType: string
{
    case app_user_guard = 'app_user_guard';
    case dash_user_guard = 'dash_user_guard';
}
