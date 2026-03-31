<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\Vault;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VaultService
{
    public function list($request)
    {
        return Vault::with('owner')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function vaultTransactions(Vault $vault, $request)
    {
        $query = VaultTransaction::with(
            'fromVault.owner',
            'toVault.owner',
            'actionBy',
            'reference'
        );

        if ($request->direction === 'in') {
            $query->where('to_vault_id', $vault->id);
        } elseif ($request->direction === 'out') {
            $query->where('from_vault_id', $vault->id);
        } else {
            $query->where(function ($q) use ($vault) {
                $q->where('to_vault_id', $vault->id)
                    ->orWhere('from_vault_id', $vault->id);
            });
        }

        return $query
            ->filterBy($request->except('direction'))
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $vault = Vault::create([
                'balance' => $data['balance'],
                'owner_id' => $data['owner_id'],

            ]);
            $vault->load('owner');

            return $vault;
        });
    }

    public function update($balance, ?Vault $vault)
    {
        $user = Auth::user();
        if ($vault == null) {
            $vault = Vault::findOrFail(1);
        }

        $toBalanceBefore = $vault->balance;

        if ($toBalanceBefore == $balance) {
            throw new CustomException('الرصيد نفسه الموجود بالفعل.');
        }
        $vault->update([
            'balance' => $balance,
        ]);

        $toBalanceAfter = $vault->balance;

        /**
         * Create vault transaction
         */
        VaultTransaction::create([
            'to_vault_id' => $vault->id,

            'type' => VaultTransactionType::ADJUSTMENT->value,

            'amount' => $toBalanceAfter - $toBalanceBefore,

            'transaction_date' => now(),

            'notes' => 'تعديل على الخزنة من super admin',

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'to_vault_balance_before' => $toBalanceBefore,
            'to_vault_balance_after' => $toBalanceAfter,
        ]);
        $vault->load('owner');

        return $vault;
    }
    public function show(Vault $vault)
    {
        $vault->load('owner');
        return $vault;
    }

    public function delete(Vault $vault)
    {
        if ($vault->balance > 0 || $vault->id == 1 || $vault->transactions()->exist()) {
            return false;
        }
        return $vault->delete();
    }


    public function selectAvailable()
    {

        $vaults = Vault::with('owner')->orderBy('id')->get([
            'id',
            'owner_id',
            'balance'
        ]);

        return $vaults;
    }
}
