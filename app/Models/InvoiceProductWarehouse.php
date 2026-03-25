<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceProductWarehouse extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;
    protected $fillable = [
        'invoice_id',
        'product_id',
        'added_quantity',
    ];
    protected $casts = [];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
