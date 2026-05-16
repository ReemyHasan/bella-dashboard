<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\Vault;
use App\Models\VaultTransaction;
use App\Models\VaultTransfer;

class VaultService
{

    public function vaultTransactions($request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Warehouse Keeper')) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }

        $vault = Vault::with('owner')->where('owner_id', $user->id)->first();
        if (!$vault) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
        $query = VaultTransaction::with(
            'fromVault.owner',
            'toVault.owner',
            'actionBy',
            'reference',
            'balanceUser'
        );

        if ($request->direction == 'in') {
            $query->where('to_vault_id', $vault->id);
        } elseif ($request->direction == 'out') {
            $query->where('from_vault_id', $vault->id);
        } else {
            $query->where(function ($q) use ($vault) {
                $q->where('to_vault_id', $vault->id)
                    ->orWhere('from_vault_id', $vault->id);
            });
        }

        return [
            'balance' => $vault->balance,
            'vault_id' => $vault->id,
            "transactions" => $query
                ->filterBy($request->except('direction'))
                ->sortBy($request->get('sort', ['created_at' => 'desc']))
                ->latest()
                ->paginate(PaginationEnum::GeneralPagination->value)
        ];
    }


    public function vaultTransfers($request)
    {
        $user = auth()->user();
        if (!$user->hasRole('Warehouse Keeper')) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
        $vault = Vault::with('owner')->where('owner_id', $user->id)->first();
        if (!$vault) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
        return VaultTransfer::query()
            ->with('toVault.owner', 'fromVault.owner', 'createdBy')

            ->where(function ($q) use ($vault) {

                $q->where('from_vault_id', $vault->id)
                    ->orWhere('to_vault_id', $vault->id);
            })

            ->filterBy($request->all())

            ->sortBy($request->get('sort', [
                'created_at' => 'desc'
            ]))

            ->latest()

            ->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function showVaultTransfer(VaultTransfer $vaultTransfer)
    {
        $user = auth()->user();
        if (!$user->hasRole('Warehouse Keeper')) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
        $vault = Vault::with('owner')->where('owner_id', $user->id)->first();
        if (!$vault) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }

        if (
            $vaultTransfer->from_vault_id != $vault->id &&
            $vaultTransfer->to_vault_id != $vault->id
        ) {
            throw new CustomException(
                'ليس لديك صلاحية للوصول لهذا التحويل'
            );
        }

        $vaultTransfer->load(
            'toVault.owner',
            'fromVault.owner',
            'createdBy'
        );

        return $vaultTransfer;
    }
}
