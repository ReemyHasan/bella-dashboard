<?php

namespace App\Enums;

enum FinancialAdjustmentType: string
{
    case BONUS_REQUEST = 'bonus_request';
    case DEDUCTION_REQUEST = 'deduction_request';
    case BONUS_ORDER = 'bonus_order';
    case DEDUCTION_ORDER = 'deduction_order';

    public function label(): string
    {
        return match ($this) {
            self::BONUS_REQUEST => 'طلب مكافأة',
            self::DEDUCTION_REQUEST => 'طلب خصم',
            self::BONUS_ORDER => 'أمر مكافأة',
            self::DEDUCTION_ORDER => 'أمر خصم',
        };
    }
}