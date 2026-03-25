<?php

namespace App\Enums;

enum VaultTransactionType: string
{
    case CASH_REQUEST = 'cash_request';
    case BONUS = 'bonus';
    case DEDUCTION = 'deduction';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
}