<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportantProduct extends Model
{
    protected $fillable = [
        'product_id',
        'created_by_type',
        'created_by_id',
        'important_for_type',
        'important_for_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function importantFor()
    {
        return $this->morphTo();
    }
}