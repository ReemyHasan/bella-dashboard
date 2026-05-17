<?php

namespace App\Enums;

use App\Notifications\Handlers\CashRequestUpdaterHandler;
use App\Notifications\Handlers\CompetitionGoalAchievementHandler;
use App\Notifications\Handlers\DeductOnProductHandler;
use App\Notifications\Handlers\FinancialMovementHandler;
use App\Notifications\Handlers\MessageHandler;
use App\Notifications\Handlers\NewCompetitionHandler;
use App\Notifications\Handlers\NewCustomerOrderHandler;
use App\Notifications\Handlers\NewMarketerHandler;
use App\Notifications\Handlers\NewOfferHandler;
use App\Notifications\Handlers\NewProductHandler;
use App\Notifications\Handlers\OrderNoteHandler;
use App\Notifications\Handlers\OrderStatusChangeHandler;
use App\Notifications\Handlers\UpdateOfferHandler;
use App\Notifications\Handlers\UpdateProductHandler;

enum NotificationType: string
{
    case MESSAGE = 'message'; // *********************
    case NEW_COMPETITION = 'new_competition'; // *********************
    case COMPETITION_GOAL_ACHIEVEMENT = 'competition_goal_achievement'; // *********************

    case NEW_CUSTOMER_ORDER = 'new_customer_order'; //  *********************
    case ORDER_STATUS_CHANGE = 'order_status_change'; //  *********************


    case ORDER_NOTE = 'order_new_note'; // *********************


    case NEW_PRODUCT = 'new_product'; // all marketers
    case UPDATE_PRODUCT = 'update_product'; // all marketers

    case NEW_OFFER = 'new_offer'; // all marketers
    case UPDATE_OFFER = 'update_offer'; // all marketers

    case DEDUCT_ON_PRODUCT = 'deduct_on_product'; // all marketers


    case FINANCIAL_MOVEMENT = 'financial_movement'; // related marketer

    case CASH_REQUEST_UPDATE = 'cash_request_update'; // related marketer, and warehouse man when admin approve the cash request

    case NEW_MARKETER = 'new_marketer'; // manager of the team this marketer registerd in


    public function label(): string
    {
        return match ($this) {
            self::MESSAGE => 'رسالة',

            self::NEW_COMPETITION => 'مسابقة جديدة',
            self::COMPETITION_GOAL_ACHIEVEMENT => 'تحقيق هدف المسابقة',

            self::NEW_CUSTOMER_ORDER => 'طلب عميل جديد',
            self::ORDER_STATUS_CHANGE => 'تغيير حالة الطلب',
            self::ORDER_NOTE => 'ملاحظة جديدة على الطلب',

            self::NEW_PRODUCT => 'منتج جديد',
            self::UPDATE_PRODUCT => 'تحديث المنتج',

            self::NEW_OFFER => 'عرض جديد',
            self::UPDATE_OFFER => 'تحديث العرض',

            self::DEDUCT_ON_PRODUCT => 'تعديل على سعر المنتج',

            self::FINANCIAL_MOVEMENT => 'حركة مالية على محفظتك',

            self::CASH_REQUEST_UPDATE => 'تحديث طلب رصيد',

            self::NEW_MARKETER => 'مسوق جديد',
            default => $this->value,
        };
    }


    public function handler(): string
    {
        return match ($this) {
            self::MESSAGE => MessageHandler::class, // ###########################

            self::NEW_COMPETITION => NewCompetitionHandler::class,  // ###########################
            self::COMPETITION_GOAL_ACHIEVEMENT => CompetitionGoalAchievementHandler::class, // ###########################

            self::NEW_CUSTOMER_ORDER => NewCustomerOrderHandler::class, // ###########################
            self::ORDER_STATUS_CHANGE => OrderStatusChangeHandler::class, // ###########################
            self::ORDER_NOTE => OrderNoteHandler::class, // ###########################

            self::NEW_PRODUCT => NewProductHandler::class,  // ###########################
            self::UPDATE_PRODUCT => UpdateProductHandler::class,  // ###########################

            self::NEW_OFFER => NewOfferHandler::class,  // ###########################
            self::UPDATE_OFFER => UpdateOfferHandler::class,  // ###########################

            self::DEDUCT_ON_PRODUCT => DeductOnProductHandler::class, // ###########################

            self::FINANCIAL_MOVEMENT => FinancialMovementHandler::class,  // ###########################
            self::CASH_REQUEST_UPDATE => CashRequestUpdaterHandler::class, // ###########################
            self::NEW_MARKETER => NewMarketerHandler::class,  // ###########################
        };
    }
}
