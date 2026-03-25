<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Enums\VaultTransferStatus;
use App\Exceptions\CustomException;
use App\Models\VaultTransaction;
use App\Models\VaultTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VaultTransferService
{
    public function list($request)
    {
        return VaultTransfer::with('toVault.owner', 'fromVault.owner', 'createdBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $vaultTransfer = VaultTransfer::create([
                'from_vault_id' => $data['from_vault_id'],
                'to_vault_id' => $data['to_vault_id'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'status' => VaultTransferStatus::PENDING->value,

                'created_by_type' => get_class($user),
                'created_by_id' => $user->id,

            ]);
            $vaultTransfer->load('toVault.owner', 'fromVault.owner', 'createdBy');

            return $vaultTransfer;
        });
    }

    public function update(VaultTransfer $vaultTransfer, array $data)
    {
        if ($vaultTransfer->status !== VaultTransferStatus::PENDING->value) {
            throw new CustomException('لا يمكن تعديل المناقلة بعد تأكيدها.');
        }
        $user = Auth::user();
        return DB::transaction(function () use ($vaultTransfer, $data, $user) {
            $vaultTransfer->update([
                'from_vault_id' => $data['from_vault_id'],
                'to_vault_id' => $data['to_vault_id'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,

                'created_by_type' => get_class($user),
                'created_by_id' => $user->id,

            ]);
            $vaultTransfer->load('toVault.owner', 'fromVault.owner', 'createdBy');

            return $vaultTransfer;
        });
    }
    public function show(VaultTransfer $vaultTransfer)
    {
        $vaultTransfer->load('toVault.owner', 'fromVault.owner', 'createdBy');
        return $vaultTransfer;
    }

    public function delete(VaultTransfer $vaultTransfer)
    {
        if ($vaultTransfer->status == VaultTransferStatus::CONFIRMED->value) {
            throw new CustomException('لا يمكن حذف المناقلة بعد تأكيدها.');
        }
        return $vaultTransfer->delete();
    }

    public function confirm(VaultTransfer $vaultTransfer)
    {
        $user = Auth::user();

        if ($vaultTransfer->status != VaultTransferStatus::PENDING->value) {
            throw new CustomException('تمت معالجة هذه المناقلة مسبقاً.');
        }

        return DB::transaction(function () use ($vaultTransfer, $user) {

            $fromVault = $vaultTransfer->fromVault()->lockForUpdate()->first();
            $toVault = $vaultTransfer->toVault()->lockForUpdate()->first();

            $amount = $vaultTransfer->amount;

            if ($fromVault->balance < $amount) {
                throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
            }

            $fromBalanceBefore = $fromVault->balance;
            $toBalanceBefore = $toVault->balance;

            $fromBalanceAfter = $fromBalanceBefore - $amount;
            $toBalanceAfter = $toBalanceBefore + $amount;

            /**
             * Update vault balances
             */
            $fromVault->update([
                'balance' => $fromBalanceAfter
            ]);

            $toVault->update([
                'balance' => $toBalanceAfter
            ]);

            /**
             * Create vault transaction
             */
            VaultTransaction::create([
                'from_vault_id' => $fromVault->id,
                'to_vault_id' => $toVault->id,

                'type' => VaultTransactionType::TRANSFER->value,

                'amount' => $amount,

                'transaction_date' => now(),

                'reason' => 'مناقلة بين الخزنات',
                'notes' => $vaultTransfer->notes,

                'reference_type' => VaultTransfer::class,
                'reference_id' => $vaultTransfer->id,

                'action_by_type' => get_class($user),
                'action_by_id' => $user->id,

                'from_vault_balance_before' => $fromBalanceBefore,
                'from_vault_balance_after' => $fromBalanceAfter,

                'to_vault_balance_before' => $toBalanceBefore,
                'to_vault_balance_after' => $toBalanceAfter,
            ]);

            /**
             * Update transfer status
             */
            $vaultTransfer->update([
                'status' => 'confirmed',
                'transferred_at' => now()
            ]);

            return $vaultTransfer->fresh([
                'toVault.owner',
                'fromVault.owner',
                'createdBy'
            ]);
        });
    }

    public function cancel(VaultTransfer $vaultTransfer)
    {
        if ($vaultTransfer->status !== VaultTransferStatus::PENDING->value) {
            throw new CustomException('لا يمكن إلغاء المناقلة بعد معالجتها.');
        }

        $vaultTransfer->update([
            'status' => VaultTransferStatus::CANCELLED->value
        ]);

        return $vaultTransfer;
    }
}
