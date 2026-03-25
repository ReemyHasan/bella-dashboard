<?php

namespace App\Enums;

enum HandOverStatus: string
{
    case pending = 'pending';
    case approved = 'approved';
    case rejected = 'rejected';
    case in_transit = 'in_transit';
    case delivered = 'delivered';
    case completed = 'completed';
    case cancelled = 'cancelled';

}
