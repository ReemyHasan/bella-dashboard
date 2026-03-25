<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vault extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;


    protected $fillable = [
        'owner_id',
        'balance'
    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted"
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];
    public function owner()
    {
        return $this->belongsTo(AppUser::class, 'owner_id');
    }
    public function fromTransactions()
    {
        return $this->hasMany(VaultTransaction::class, 'from_vault_id');
    }
    public function toTransactions()
    {
        return $this->hasMany(VaultTransaction::class, 'to_vault_id');
    }
}
