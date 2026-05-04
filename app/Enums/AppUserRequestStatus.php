<?php

namespace App\Enums;

enum AppUserRequestStatus: string
{
    case pending = 'pending';
    case approved = 'approved';
    case rejected = 'rejected';
}
