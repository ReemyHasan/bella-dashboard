<?php

namespace App\Http\Requests\DashUser\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserPasswordRequest extends FormRequest
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
            'new_password' => [
                'required',
                'string',
                'min:8',
                // 'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W)(?!.* ).{8,30}$/',
                'confirmed'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'new_password' => 'كلمة المرور الجديدة',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.regex' => 'يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة وأرقام ورمز خاص.',
        ];
    }
}
