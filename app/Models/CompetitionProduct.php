<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionProduct extends Model
{
    protected $fillable = [
        'competition_id',
        'product_id',
        'target_quantity',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
