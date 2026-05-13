<?php

namespace App\Http\Requests\Mobile\AppUser;

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
        $user = auth()->user();
        return ($user->hasRole('Team Manager') || $user->hasRole('Team Leader'));
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
                'deduction_request'
            ])],
            'requested_for_id' => ['required', 'integer'],
            'reason' => ['required', 'string'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $type = $this->input('type');
            $requestedForId = $this->input('requested_for_id');
            $appUser = auth()->user();

            $requestedById = $appUser->id;

            // =========================
            // ✅ REQUEST → ONLY APP USER
            // =========================
            if (str_contains($type, 'request')) {

                $targetUser = AppUser::find($requestedForId);

                if (!$targetUser) {
                    $validator->errors()->add('requested_for_id', 'المستخدم غير موجود.');
                    return;
                }

                $isManager = $appUser->hasRole('Team Manager');
                $isLeader  = $appUser->hasRole('Team Leader');

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
            'amount' => "المبلغ المطلوب",
            'requested_for_id' => "طلب من أجل",
            'notes' => "ملاحظات",
            'reason' => "السبب",

        ];
    }
}
