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
}
