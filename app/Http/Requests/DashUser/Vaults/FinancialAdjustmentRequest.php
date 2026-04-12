<?php

namespace App\Http\Requests\DashUser\Vaults;

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

            // 'vault_id' => ['required', 'exists:vaults,id'],

            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['required', 'in:bonus,deduction'],

            'requested_for_type' => ['required', 'in:dash_user,app_user'],
            'requested_for_id' => ['required', 'integer'],

            'reason' => ['required', 'string'],

            'notes' => ['nullable', 'string'],
        ];
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
