<?php

namespace App\Enums;

enum OrderStatus: string
{
    case new = 'new';
    case delivering = 'delivering';
    case waiting = 'waiting';
    case completed = 'completed';
    case cancelled = 'cancelled';
    case refund = 'refund';
}
