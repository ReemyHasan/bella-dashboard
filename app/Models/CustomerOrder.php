<?php

namespace App\Models;

use App\Observers\CustomerOrderObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([CustomerOrderObserver::class])]
class CustomerOrder extends Model
{

    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'order_number',
        'customer_id',
        'address_id',
        'address_details',
        'customer_mobile',
        'app_user_id',
        'marketer_percentage',
        'warehouse_man_id',
        'delivery_cost',
        // 'delivery_additional_cost',
        'teamleader_id',
        'teamleader_percentage',
        'manager_id',
        'manager_percentage',
        'warehouse_id',
        'order_status',
        'total_base_price',
        'total_price',
        'additional_tips',
        // 'deduction_amount',
        // 'deduction_type',
        'current_exchange_rate',
        'currency_id',
        'cancellation_reason',
        'notes',
        'placed_at',
        'waiting_until',
        'delivered_at',
        'cancelled_at',

        'created_by_type',
        'created_by_id',

        'reviewed_by',
        'reviewed_at',

        'marketer_amount',
        'teamleader_amount',
        'manager_amount',
        'warehouse_man_amount',
        'is_financial_processed',
        'is_stock_reserved',
        'team_id',
        'zone_id',
        'sub_team_id',
        'waiting_reason',



        'adjustment_type', //percentage,fixed
        'adjustment_operation', ////increase,decrease
        'adjustment_value',
        'is_target',
        'competition_id'


    ];

    protected $casts = [
        'marketer_percentage' => 'decimal:2',
        'teamleader_percentage' => 'decimal:2',
        'current_exchange_rate' => 'decimal:4',
        'manager_percentage' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'adjustment_value' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_base_price' => 'decimal:2',
        'additional_tips' => 'decimal:2',

        'marketer_amount' => 'decimal:2',
        'teamleader_amount' => 'decimal:2',
        'manager_amount' => 'decimal:2',
        'is_target' => 'boolean',

        // 'warehouse_man_amount' => 'decimal:2',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "placed_at_formatted",
        "waiting_until_formatted",
        "delivered_at_formatted",
        "cancelled_at_formatted",
        "reviewed_at_formatted",
    ];

    public function getFinalTotalPriceAttribute()
    {
        return $this->total_price + $this->additional_tips + $this->delivery_cost;
    }

    public function getPlacedAtFormattedAttribute()
    {
        return $this->placed_at
            ? showDateTime($this->placed_at)
            : null;
    }
    public function getWaitingUntilFormattedAttribute()
    {
        return $this->waiting_until
            ? showDateTime($this->waiting_until)
            : null;
    }

    public function getDeliveredAtFormattedAttribute()
    {
        return $this->delivered_at
            ? showDateTime($this->delivered_at)
            : null;
    }

    public function getReviewedAtFormattedAttribute()
    {
        return $this->reviewed_at
            ? showDateTime($this->reviewed_at)
            : null;
    }

    public function getCancelledAtFormattedAttribute()
    {
        return $this->cancelled_at
            ? showDateTime($this->cancelled_at)
            : null;
    }
    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function offers()
    {
        return $this->hasMany(OrderOffer::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
    public function subTeam()
    {
        return $this->belongsTo(SubTeam::class, 'sub_team_id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function marketer()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    public function warehouseMan()
    {
        return $this->belongsTo(AppUser::class, 'warehouse_man_id');
    }

    public function teamleader()
    {
        return $this->belongsTo(AppUser::class, 'teamleader_id');
    }
    public function manager()
    {
        return $this->belongsTo(AppUser::class, 'manager_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function reviewedBy()
    {
        return $this->belongsTo(DashUser::class, 'reviewed_by');
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }
}
