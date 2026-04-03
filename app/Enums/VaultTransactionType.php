<?php

namespace App\Enums;

enum VaultTransactionType: string
{
    case CASH_REQUEST = 'cash_request';
    case BONUS = 'bonus';
    case DEDUCTION = 'deduction';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case ORDER_COMPANY_PROFIT = 'order_company_revenue';
    case ORDER_REFUND = 'order_refund';
    case ORDER_COMPLETE = 'order_complete';



}