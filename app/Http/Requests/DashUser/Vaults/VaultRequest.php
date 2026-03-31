<?php

namespace App\Http\Requests\DashUser\Vaults;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaultRequest extends FormRequest
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
        $vaultId = $this->route('vault')?->id;

        return [

            'balance' => 'required|numeric',

            'owner_id' => [
                'required',

                // must exist AND be warehouse man
                Rule::exists('app_users', 'id')->where(function ($query) {
                    $query->where('is_warehouse_man', true);
                }),

                // must be unique in vaults (one vault per user)
                Rule::unique('vaults', 'owner_id')->ignore($vaultId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_id.exists' => 'يجب أن يكون مالك الخزنة أمين مستودع.',
            'owner_id.unique' => 'هذا المستخدم لديه بالفعل خزنة.',
            'owner_id.required' => 'حقل مالك الخزنة مطلوب.',
        ];
    }
    public function attributes(): array
    {
        return [
            'balance' => 'الرصيد',
            'owner_id' => 'مالك الخزنة'
        ];
    }
}
