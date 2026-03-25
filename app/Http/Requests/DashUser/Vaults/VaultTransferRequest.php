<?php

namespace App\Http\Requests\DashUser\Vaults;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaultTransferRequest extends FormRequest
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
            'from_vault_id' => [
                'required',
                'exists:vaults,id',
                'different:to_vault_id'
            ],

            'to_vault_id' => [
                'required',
                'exists:vaults,id'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ]
        ];
    }


    public function attributes(): array
    {
        return [
            'from_vault_id' => 'الخزنة المصدر',
            'to_vault_id' => 'الخزنة الهدف',
            'amount' => 'المبلغ',
            'notes' => 'الملاحظات',
        ];
    }
}
