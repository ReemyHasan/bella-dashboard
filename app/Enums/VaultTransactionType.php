<?php

namespace App\Enums;

enum VaultTransactionType: string
{
    case CASH_REQUEST = 'cash_request_complete';
    case BONUS = 'bonus';
    case DEDUCTION = 'deduction';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case ORDER_COMPANY_PROFIT = 'order_company_revenue';
    case ORDER_REFUND = 'order_refund';
    case ORDER_COMPLETE = 'order_complete';
    case CASH_REQUEST_APPROVED = 'cash_request_approve';

        // ✅ New types
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';

    case refund_marketer = 'refund_marketer';
    case refund_teamleader = 'refund_teamleader';
    case refund_manager = 'refund_manager';

    case marketer_percentage = 'marketer_percentage';
    case teamleader_percentage = 'teamleader_percentage';
    case manager_percentage = 'manager_percentage';

    case BONUS_REQUEST = 'bonus_request';
    case DEDUCTION_REQUEST = 'deduction_request';
    case BONUS_ORDER = 'bonus_order';
    case DEDUCTION_ORDER = 'deduction_order';
    case COMPETITION_PRIZE = 'competition_prize';
    case SOURCE_COMPETITION_PRIZE_DEDUCT = 'source_competition_prize_deduct';


    public function label(): string
    {
        return match ($this) {
            self::CASH_REQUEST          => 'طلب نقدي مكتمل',
            self::CASH_REQUEST_APPROVED => 'طلب نقدي موافق عليه',

            self::BONUS                 => 'مكافأة',
            self::DEDUCTION             => 'خصم',

            self::BONUS_REQUEST => 'طلب مكافأة',
            self::DEDUCTION_REQUEST => 'طلب خصم',
            self::BONUS_ORDER => 'أمر مكافأة',
            self::DEDUCTION_ORDER => 'أمر خصم',

            self::TRANSFER              => 'تحويل',
            self::TRANSFER_IN           => 'تحويل وارد',
            self::TRANSFER_OUT          => 'تحويل صادر',
            self::ADJUSTMENT            => 'تعديل',
            self::ORDER_COMPLETE        => 'إتمام طلب',
            self::ORDER_REFUND          => 'استرجاع طلب',
            self::ORDER_COMPANY_PROFIT  => 'ربح الشركة من الطلب',
            self::refund_marketer       => 'استرجاع من مسوق',
            self::refund_teamleader     => 'استرجاع من قائد فريق',
            self::refund_manager        => 'استرجاع من مدير',

            self::marketer_percentage        => 'نسبة مسوق',
            self::teamleader_percentage        => 'نسبة قائد فريق فرعي',
            self::manager_percentage        => 'نسبة مدير فريق',
            self::COMPETITION_PRIZE        => 'جائزة مسابقة',
            self::SOURCE_COMPETITION_PRIZE_DEDUCT => 'خصم جائزة المسابقة من المدير'

        };
    }
}

