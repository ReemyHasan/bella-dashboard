<?php

namespace App\Enums;

enum CompetitionType: string
{
    case financial_amount = 'financial_amount';
    case orders_count = 'orders_count';
    case product_sales = 'product_sales';

    case offer_sales = 'offer_sales';
    case general_product_sales = 'general_product_sales';
}
