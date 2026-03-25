<?php

namespace App\Enums;

enum OrderStatus: string
{
    case pending = 'pending';
    case approved = 'approved';
    case delivering = 'delivering';

    case processing = 'processing';
    case completed = 'completed';
    case rejected = 'rejected';
    case cancelled = 'cancelled';
    case refund = 'refund';
}
