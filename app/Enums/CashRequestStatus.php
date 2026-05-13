<?php

namespace App\Enums;

enum CashRequestStatus: string
{

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case WAITING_DELIVERY_APPROVE = 'waiting_delivery_approve';
    case NOT_DELIVERED = 'not_delivered';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'معلق',
            self::APPROVED => 'تمت الموافقة',
            self::IN_TRANSIT => 'قيد التوصيل',
            self::DELIVERED => 'تم التسليم',
            self::NOT_DELIVERED => 'لم يتم التسليم',
            self::CANCELLED => 'ملغي',
            self::COMPLETED => 'مكتمل',
            self::WAITING_DELIVERY_APPROVE => 'بانتظار تأكيد التسليم',
            default => $this->value,
        };
    }
}
