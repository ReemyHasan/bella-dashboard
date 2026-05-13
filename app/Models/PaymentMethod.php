<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'required_fields'
    ];
    protected $casts = [

        'required_fields' => 'array',
    ];
}
