<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\BalanceTransferRequest;
use App\Models\DashUser;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceTransferRequestService
{
    public function list($request)
    {
        return BalanceTransferRequest::with('fromUser', 'toUser', 'reviewedBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function show(BalanceTransferRequest $balance_transfer_request)
    {
        $balance_transfer_request->load('fromUser', 'toUser', 'reviewedBy');
        return $balance_transfer_request;
    }

    // public function delete(balance_transfer_request $balance_transfer_request)
    // {
    //     if ($balance_transfer_request->status == 'approved') {
    //         throw new CustomException('لا يمكن حذف الطلب بعد معالجته.');
    //     }
    //     return $balance_transfer_request->delete();
    // }


    public function handle(BalanceTransferRequest $balance_transfer_request, array $data)
    {
        $status = $data['status'];

        return match ($status) {
            'approved' =>
            $this->approve(
                $balance_transfer_request,
                $data['notes'] ?? null
            ),

            'rejected' =>
            $this->reject(
                $balance_transfer_request,
                $data['notes'] ?? null
            ),

            default => throw new CustomException('يوجد مشكلة بالمعلومات المدخلة.')
        };
    }


    public function approve(BalanceTransferRequest $request, ?string $notes = null)
    {
        if ($request->status !== 'pending') {
            throw new CustomException('تمت معالجة الطلب مسبقاً.');
        }

        return DB::transaction(function () use ($request, $notes) {

            $from = $request->fromUser()->lockForUpdate()->first();
            $to = $request->toUser()->lockForUpdate()->first();

            if ($from->balance < $request->amount) {
                throw new CustomException("رصيد المرسل غير كافٍ {$from->balance}.");
            }

            $amount = $request->amount;

            $fromBefore = $from->balance;
            $toBefore = $to->balance;

            $fromAfter = $fromBefore - $amount;
            $toAfter = $toBefore + $amount;

            // ✅ Update balances
            $from->update(['balance' => $fromAfter]);
            $to->update(['balance' => $toAfter]);

            // ✅ Update request
            $request->update([
                'status' => 'approved',
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // =========================
            // ✅ Transactions
            // =========================

            // Sender
            VaultTransaction::create([
                'balance_user_type' => AppUser::class,
                'balance_user_id' => $from->id,
                'type' => VaultTransactionType::TRANSFER_OUT->value,
                'amount' => $amount,

                'reference_type' => BalanceTransferRequest::class,
                'reference_id' => $request->id,

                'to_vault_balance_before' => $fromBefore,
                'to_vault_balance_after' => $fromAfter,

                'action_by_type' => get_class(Auth::user()),
                'action_by_id' => Auth::id(),
            ]);

            // Receiver
            VaultTransaction::create([
                'balance_user_type' => AppUser::class,
                'balance_user_id' => $to->id,
                'type' => VaultTransactionType::TRANSFER_IN->value,
                'amount' => $amount,

                'reference_type' => BalanceTransferRequest::class,
                'reference_id' => $request->id,

                'to_vault_balance_before' => $toBefore,
                'to_vault_balance_after' => $toAfter,

                'action_by_type' => get_class(Auth::user()),
                'action_by_id' => Auth::id(),
            ]);

            return $request->refresh();
        });
    }
    public function reject(BalanceTransferRequest $balance_transfer_request, ?string $notes = null)
    {
        if ($balance_transfer_request->status !== 'pending') {
            throw new CustomException('لا يمكن رفض على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($balance_transfer_request, $notes) {

            $balance_transfer_request->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => 'rejected',
            ]);

            return $balance_transfer_request->refresh();
        });
    }
}
