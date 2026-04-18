<?php

namespace App\Http\Requests\DashUser\Vaults;

use App\Models\AppUser;
use App\Models\DashUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'amount' => ['required', 'numeric', 'min:1'],
            // 'type' => ['required', 'in:bonus,deduction'],
            'type' => ['required', Rule::in([
                'bonus_request',
                'deduction_request',
                'bonus_order',
                'deduction_order',
            ])],
            'requested_for_type' => ['required', 'in:dash_user,app_user'],
            'requested_for_id' => ['required', 'integer'],

            'requested_by_id' => ['nullable', 'integer', 'exists:app_users,id'],

            'reason' => ['required', 'string'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $type = $this->input('type');
            $requestedForType = $this->input('requested_for_type');
            $requestedForId = $this->input('requested_for_id');

            $requestedById = $this->input('requested_by_id');
            $requestedByType = str_ends_with($type, '_order') ? DashUser::class : AppUser::class;
            $user = auth()->user();

            if (str_contains($type, 'request') && !$requestedById) {
                $validator->errors()->add('requested_by_id', 'في حال كان النوع طلب يجب تحديد المدير الطالب له.');
                return;
            }

            // =========================
            // ✅ ORDER → ONLY DASH USER
            // =========================
            if (str_contains($type, 'order')) {

                if (!$user instanceof DashUser) {
                    $validator->errors()->add('type', 'فقط مستخدم لوحة التحكم يمكنه إنشاء أمر.');
                    return;
                }
            }

            // =========================
            // ✅ REQUEST → ONLY APP USER
            // =========================
            if (str_contains($type, 'request')) {

                $appUser = AppUser::with('roles')->find($requestedById);
                if (!$appUser) {
                    $validator->errors()->add('requested_by_id', 'المستخدم الطالب غير موجود.');
                    return;
                }
                $isManager = $appUser->hasRole('Team Manager');
                $isLeader  = $appUser->hasRole('Team Leader');

                if (!$isManager && !$isLeader) {
                    $validator->errors()->add('type', 'يجب أن يكون الطالب قائد فريق أو مدير.');
                    return;
                }

                if ($requestedForType !== 'app_user') {
                    $validator->errors()->add('requested_for_type', 'الطلب يجب أن يكون لمستخدم تطبيق.');
                    return;
                }
                $targetUser = AppUser::find($requestedForId);

                if (!$targetUser) {
                    $validator->errors()->add('requested_for_id', 'المستخدم غير موجود.');
                    return;
                }

                // =========================
                // 🎯 MANAGER → same team
                // =========================
                if ($isManager) {
                    if ($targetUser->team_id != $appUser->team_id) {
                        $validator->errors()->add('requested_for_id', 'المستخدم يجب أن يكون من نفس الفريق.');
                    }
                }

                // =========================
                // 🎯 LEADER → same subteam
                // =========================
                if ($isLeader) {
                    if ($targetUser->subteam_id != $appUser->subteam_id) {
                        $validator->errors()->add('requested_for_id', 'المستخدم يجب أن يكون من نفس الفريق الفرعي.');
                    }
                }
            }
        });
    }
    public function attributes(): array
    {
        return [
            // 'vault_id' => "من خزنة",

            'amount' => "المبلغ المطلوب",

            'requested_for_type' => "طلب من أجل",
            'requested_for_id' => "طلب من أجل",
            'notes' => "ملاحظات",
            'reason' => "السبب",

        ];
    }
}
