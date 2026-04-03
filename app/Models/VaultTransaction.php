<?php

namespace App\Models;

use App\Traits\HasFilters;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultTransaction extends Model
{
    use HasFactory, HasFilters, HasFormattedTimestamps;

    protected $fillable = [
        'from_vault_id',
        'to_vault_id',
        'type',
        'amount',
        'transaction_date',
        'reason',
        'notes',

        'reference_type',
        'reference_id',


        'action_by_type',
        'action_by_id',

        'from_vault_balance_before',
        'from_vault_balance_after',

        'to_vault_balance_before',
        'to_vault_balance_after',

        'balance_user_type',
        'balance_user_id',
        // 'currency',
        // 'reference_number'

    ];
    protected $appends = [
        "created_at_formatted",
        "updated_at_formatted",
        "transaction_date_formatted",
        "direction"

    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'from_vault_balance_before' => 'decimal:2',
        'from_vault_balance_after' => 'decimal:2',
        'to_vault_balance_before' => 'decimal:2',
        'to_vault_balance_after' => 'decimal:2',

    ];
    public function getTransactionDateFormattedAttribute()
    {
        return $this->transaction_date
            ? showDateTime($this->transaction_date)
            : null;
    }

    public function directionForVault($vaultId)
    {

        if ($this->to_vault_id == $vaultId) {
            return 'in';
        }

        if ($this->from_vault_id == $vaultId) {
            return 'out';
        }

        return null;
    }
    public function fromVault()
    {
        return $this->belongsTo(Vault::class, 'from_vault_id');
    }
    public function toVault()
    {
        return $this->belongsTo(Vault::class, 'to_vault_id');
    }

    public function actionBy()
    {
        return $this->morphTo();
    }
    public function reference()
    {
        return $this->morphTo();
    }
    public function balanceUser()
    {
        return $this->morphTo();
    }
}
