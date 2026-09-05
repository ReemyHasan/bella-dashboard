<?php

namespace App\Http\Requests\DashUser\Vaults;

use Illuminate\Foundation\Http\FormRequest;

class BalanceTransferRequestRequest extends FormRequest
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
        $rules = [
            'amount' => ['required', 'numeric', 'min:1'],
            'from_user_id' => ['required', 'integer', 'exists:app_users,id'],
            'to_user_id' => ['required', 'integer', 'exists:app_users,id'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];


        return $rules;
    }


    public function messages(): array
    {
        return [

            'amount.required' => 'المبلغ مطلوب.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقم.',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',

            'to_user_id.required' => 'يجب تحديد المستلم.',
            'to_user_id.integer' => 'المستلم غير موجود.',


            'from_user_id.required' => 'يجب تحديد المسوق المصدر.',
            'from_user_id.integer' => 'المسوق المصدر غير موجود.',

        ];
    }
    public function attributes(): array
    {
        return [

            'amount' => "المبلغ المطلوب",

            'from_user_id' => "المسوق المصدر",
            'to_user_id' => "المسوق المستلم",

            'notes' => "ملاحظات",

        ];
    }
}
