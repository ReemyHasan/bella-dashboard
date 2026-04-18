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

    public function label(): string
    {
        return match ($this) {
            self::new => 'جديد',
            self::delivering => 'قيد التوصيل',
            self::waiting => 'قيد الانتظار',
            self::completed => 'مكتمل',
            self::cancelled => 'ملغي',
            self::refund => 'مرتجع',
            default => $this->value,
        };
    }
}
