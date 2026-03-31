<?php

namespace App\Models;

use App\Observers\InvoiceObserver;
use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([InvoiceObserver::class])]
class Invoice extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'title',
        'name_of_merchant',
        'date',
        'warehouse_id',
        'is_confirmed',
        'created_by_id',
        'created_by_type',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "date_formatted"
    ];
    public function getDateFormattedAttribute()
    {
        return $this->date
            ? showDateTime($this->date, 'Y-m-d')
            : null;
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }



    public function invoiceProductWarehouses()
    {
        return $this->hasMany(InvoiceProductWarehouse::class);
    }
    public function createdBy()
    {
        return $this->morphTo();
    }
}
