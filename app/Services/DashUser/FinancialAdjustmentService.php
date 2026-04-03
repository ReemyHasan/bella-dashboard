<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\DashUser;
use App\Models\FinancialAdjustment;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialAdjustmentService
{
    public function list($request)
    {
        return FinancialAdjustment::with('fromVault.owner', 'toVault.owner', 'requestedFor', 'requestedBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {

            $financialAdjustment = FinancialAdjustment::create([
                'from_vault_id' => 1,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => 'pending',

                'requested_by_type' => get_class($user),
                'requested_by_id' => $user->id,

                'requested_for_type' => $data['requested_for_type'] == 'dash_user' ? DashUser::class : AppUser::class,
                'requested_for_id' => $data['requested_for_id']
            ]);
            $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedBy');

            return $financialAdjustment;
        });
    }

    public function update(FinancialAdjustment $financialAdjustment, array $data)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن تعديل الطلب بعد مراجعته.');
        }

        return DB::transaction(function () use ($financialAdjustment, $data) {

            $financialAdjustment->update([
                'from_vault_id' => 1,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => 'pending',
                'requested_for_type' => $data['requested_for_type'] == 'dash_user' ? DashUser::class : AppUser::class,
                'requested_for_id' => $data['requested_for_id']
            ]);
            $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedBy');

            return $financialAdjustment;
        });
    }
    public function show(FinancialAdjustment $financialAdjustment)
    {
        $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedFor', 'requestedBy', 'reviewedBy');
        return $financialAdjustment;
    }

    public function delete(FinancialAdjustment $financialAdjustment)
    {
        if ($financialAdjustment->status == 'approved') {
            throw new CustomException('لا يمكن حذف الطلب بعد معالجته.');
        }
        return $financialAdjustment->delete();
    }


    public function handle(FinancialAdjustment $financialAdjustment, array $data)
    {
        $status = $data['status'];

        return match ($status) {
            'approved' =>
            $this->approve(
                $financialAdjustment,
                $data['notes'] ?? null
            ),

            'rejected' =>
            $this->reject(
                $financialAdjustment,
                $data['notes'] ?? null
            ),

            default => throw new CustomException('يوجد مشكلة بالمعلومات المدخلة.')
        };
    }


    public function approve(FinancialAdjustment $financialAdjustment, ?string $notes = null)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن الموافقة على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($financialAdjustment, $notes) {

            $financialAdjustment->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => 'approved',
            ]);
            $financialAdjustment->refresh();
            $this->processAdjustment($financialAdjustment);
            return $financialAdjustment;
        });
    }
    public function reject(FinancialAdjustment $financialAdjustment, ?string $notes = null)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن رفض على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($financialAdjustment, $notes) {

            $financialAdjustment->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => 'rejected',
            ]);

            return $financialAdjustment->refresh();
        });
    }

    private function processAdjustment(FinancialAdjustment $adjustment)
    {
        $target = $adjustment->requestedFor;

        if ($target instanceof DashUser) {
            $this->handleDashUserAdjustment($adjustment, $target);
            return;
        }

        if ($target instanceof AppUser) {
            $this->handleAppUserAdjustment($adjustment, $target);
            return;
        }
    }


    private function handleDashUserAdjustment(FinancialAdjustment $adjustment, DashUser $user)
    {
        $fromVault = $adjustment->fromVault()->lockForUpdate()->first();

        if (!$fromVault) {
            throw new CustomException('الخزنة المصدر غير موجودة.');
        }

        $amount = $adjustment->amount;

        $userBefore = $user->balance;
        $vaultBefore = $fromVault->balance;

        if ($adjustment->type == 'bonus') {

            if ($vaultBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
            }

            $userAfter = $userBefore + $amount;
            $vaultAfter = $vaultBefore - $amount;

            $transactionType = VaultTransactionType::BONUS->value;
        } else {

            if ($userBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ.');
            }

            $userAfter = $userBefore - $amount;
            $vaultAfter = $vaultBefore + $amount;

            $transactionType = VaultTransactionType::DEDUCTION->value;
        }

        $user->update([
            'balance' => $userAfter
        ]);

        $fromVault->update([
            'balance' => $vaultAfter
        ]);

        VaultTransaction::create([

            'from_vault_id' => $fromVault->id,

            'type' => $transactionType,
            'amount' => $amount,
            'transaction_date' => now(),

            'reason' => $adjustment->reason,
            'notes' => $adjustment->notes,

            'reference_type' => FinancialAdjustment::class,
            'reference_id' => $adjustment->id,

            'balance_user_type' => DashUser::class,
            'balance_user_id' => $user->id,

            'action_by_type' => get_class(Auth::user()),
            'action_by_id' => Auth::id(),

            'from_vault_balance_before' => $vaultBefore,
            'from_vault_balance_after' => $vaultAfter,

            'to_vault_balance_before' => $userBefore,
            'to_vault_balance_after' => $userAfter,
        ]);
    }

    private function handleAppUserAdjustment(FinancialAdjustment $adjustment, AppUser $user)
    {
        if ($user->is_warehouse_man) {
            return $this->handleWarehouseManAdjustment($adjustment, $user);
        }

        return $this->handleNormalAppUserAdjustment($adjustment, $user);
    }

    private function handleNormalAppUserAdjustment(FinancialAdjustment $adjustment, AppUser $user)
    {
        $fromVault = $adjustment->fromVault()->lockForUpdate()->first();

        if (!$fromVault) {
            throw new CustomException('الخزنة المصدر غير موجودة.');
        }

        $amount = $adjustment->amount;

        $userBefore = $user->balance;
        $vaultBefore = $fromVault->balance;

        if ($adjustment->type == 'bonus') {

            if ($vaultBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
            }

            $userAfter = $userBefore + $amount;
            $vaultAfter = $vaultBefore - $amount;

            $transactionType = VaultTransactionType::BONUS->value;
        } else {

            if ($userBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ.');
            }

            $userAfter = $userBefore - $amount;
            $vaultAfter = $vaultBefore + $amount;

            $transactionType = VaultTransactionType::DEDUCTION->value;
        }

        $user->update([
            'balance' => $userAfter
        ]);

        $fromVault->update([
            'balance' => $vaultAfter
        ]);

        VaultTransaction::create([

            'from_vault_id' => $fromVault->id,

            'type' => $transactionType,
            'amount' => $amount,
            'transaction_date' => now(),

            'reason' => $adjustment->reason,
            'notes' => $adjustment->notes,

            'reference_type' => FinancialAdjustment::class,
            'reference_id' => $adjustment->id,

            
            'balance_user_type' => AppUser::class,
            'balance_user_id' => $user->id,
            'action_by_type' => get_class(Auth::user()),
            'action_by_id' => Auth::id(),

            'from_vault_balance_before' => $vaultBefore,
            'from_vault_balance_after' => $vaultAfter,

            'to_vault_balance_before' => $userBefore,
            'to_vault_balance_after' => $userAfter,
        ]);
    }

    private function handleWarehouseManAdjustment(FinancialAdjustment $adjustment, AppUser $user)
    {
        $fromVault = $adjustment->fromVault()->lockForUpdate()->first();

        if (!$fromVault) {
            throw new CustomException('الخزنة المصدر غير موجودة.');
        }

        $amount = $adjustment->amount;

        $vaultBefore = $fromVault->balance;
        $balanceBefore = $user->balance;

        if ($adjustment->type == 'bonus') {

            if ($vaultBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
            }

            // vault → user balance
            $vaultAfter = $vaultBefore - $amount;
            $balanceAfter = $balanceBefore + $amount;

            $transactionType = VaultTransactionType::BONUS->value;
        } else {

            if ($balanceBefore < $amount) {
                throw new CustomException('الرصيد غير كافٍ.');
            }

            // user balance → vault
            $vaultAfter = $vaultBefore + $amount;
            $balanceAfter = $balanceBefore - $amount;

            $transactionType = VaultTransactionType::DEDUCTION->value;
        }

        $fromVault->update([
            'balance' => $vaultAfter
        ]);

        $user->update([
            'balance' => $balanceAfter
        ]);

        VaultTransaction::create([

            'from_vault_id' => $fromVault->id,

            'type' => $transactionType,
            'amount' => $amount,
            'transaction_date' => now(),

            'reason' => $adjustment->reason,
            'notes' => $adjustment->notes,

            'reference_type' => FinancialAdjustment::class,
            'reference_id' => $adjustment->id,

            
            'balance_user_type' => AppUser::class,
            'balance_user_id' => $user->id,

            'action_by_type' => get_class(auth()->user()),
            'action_by_id' => auth()->id(),

            'from_vault_balance_before' => $vaultBefore,
            'from_vault_balance_after' => $vaultAfter,

            'to_vault_balance_before' => $balanceBefore,
            'to_vault_balance_after' => $balanceAfter,
        ]);
    }
}
