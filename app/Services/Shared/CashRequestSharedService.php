<?php

namespace App\Services\Shared;

use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\CashRequest;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;

class CashRequestSharedService
{
    public function addTransaction(CashRequest $cashRequest)
    {
        $user = Auth::user();

        $fromVault = $cashRequest->fromVault()->lockForUpdate()->first();
        $requestedAmount = $cashRequest->approved_amount;
        $exchangeValue = $cashRequest->current_exchange_value;

        $amount = $requestedAmount * $exchangeValue;

        if ($fromVault->balance < $amount) {
            throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
        }

        $fromBalanceBefore = $fromVault->balance;

        $fromBalanceAfter = $fromBalanceBefore - $amount;

        /**
         * Update vault balances
         */
        $fromVault->update([
            'balance' => $fromBalanceAfter
        ]);


        $currencyNote =
            "المطلوب: {$requestedAmount} {$cashRequest->currency->code} | " .
            "سعر الصرف: {$exchangeValue} | " .
            "المبلغ الواجب تسليمه: {$amount}";

        VaultTransaction::create([
            'from_vault_id' => $fromVault->id,

            'type' => VaultTransactionType::CASH_REQUEST->value,

            'amount' => $amount,

            'transaction_date' => now(),

            'reason' => $cashRequest->cash_request_reason,
            'notes' => $currencyNote . ' | ' . ($cashRequest->notes ?? ''),

            'reference_type' => CashRequest::class,
            'reference_id' => $cashRequest->id,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'from_vault_balance_before' => $fromBalanceBefore,
            'from_vault_balance_after' => $fromBalanceAfter,

        ]);
    }

    public function transferFromUser(CashRequest $cashRequest)
    {
        $user = Auth::user();

        $target = $cashRequest->requestedFor; // AppUser or DashUser
        // $vault = Vault::lockForUpdate()->findOrFail($cashRequest->delivered_by);
        // $fromVault = $cashRequest->fromVault()->lockForUpdate()->first();

        $requestedAmount = $cashRequest->approved_amount;
        $exchangeValue = $cashRequest->current_exchange_value;

        $amount = $requestedAmount * $exchangeValue;

        // 🔴 Check user balance
        if ($target->balance < $amount) {
            throw new CustomException('رصيد المستخدم غير كافٍ.');
        }

        $userBalanceBefore = $target->balance;
        $userBalanceAfter = $userBalanceBefore - $amount;

        // $vaultBalanceBefore = $fromVault->balance;
        // $vaultBalanceAfter = $vaultBalanceBefore + $amount;

        // ✅ Update balances
        $target->update([
            'balance' => $userBalanceAfter
        ]);

        // $fromVault->update([
        //     'balance' => $vaultBalanceAfter
        // ]);

        // 🧾 Transaction (User → Vault)
        VaultTransaction::create([
            // 'to_vault_id' => $fromVault->id,
            'balance_user_type' => AppUser::class,
            'balance_user_id' => $user->id,

            'type' => VaultTransactionType::CASH_REQUEST_APPROVED->value,

            'amount' => $amount,

            'transaction_date' => now(),

            'reason' => $cashRequest->cash_request_reason,
            'notes' => "خصم المبلغ من محفظة المستخدم من طلب الرصيد.",

            'reference_type' => CashRequest::class,
            'reference_id' => $cashRequest->id,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'from_vault_balance_before' => $userBalanceBefore,
            'from_vault_balance_after' => $userBalanceAfter,


            // 'to_vault_balance_before' => $vaultBalanceBefore,
            // 'to_vault_balance_after' => $vaultBalanceAfter,
        ]);
    }
}
