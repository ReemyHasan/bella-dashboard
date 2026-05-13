<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRequest extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'from_vault_id',

        'requested_amount',
        'approved_amount',
        'address_id',
        'address_details',
        'cash_request_reason',

        'status',
        'notes',


        'requested_by_type',
        'requested_by_id',

        'requested_for_type',
        'requested_for_id',

        'reviewed_by',
        'reviewed_at',

        'review_notes',


        'delivered_by',

        'delivery_cost',
        'additional_delivery_cost',

        'currency_id',
        'current_exchange_value',
        'payment_method_id',

        'delivered_at',
        'payment_method_fields'


    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "reviewed_at_formatted",
        "delivered_at_formatted"
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'current_exchange_value' => 'decimal:2',
        'payment_method_fields' => 'array'

    ];
    public function getReviewedAtFormattedAttribute()
    {
        return $this->reviewed_at
            ? showDateTime($this->reviewed_at)
            : null;
    }

    public function getDeliveredAtFormattedAttribute()
    {
        return $this->delivered_at
            ? showDateTime($this->delivered_at)
            : null;
    }
    public function fromVault()
    {
        return $this->belongsTo(Vault::class, 'from_vault_id');
    }

    public function requestedBy()
    {
        return $this->morphTo();
    }

    public function requestedFor()
    {
        return $this->morphTo();
    }

    public function deliveredBy()
    {
        return $this->belongsTo(AppUser::class, 'delivered_by');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }


    public function reviewedBy()
    {
        return $this->belongsTo(DashUser::class, 'reviewed_by');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
