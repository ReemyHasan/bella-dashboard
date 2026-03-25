<?php

namespace App\Http\Requests\DashUser\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'user_name'     => ['required', 'string', 'max:255', Rule::unique('customers', 'user_name')->ignore($this->customer)],
            'profile_link'     => 'nullable|string|max:255|url',
            'mobile' => [
                'required',
                'string',
                Rule::unique('customers', 'mobile')
                    ->ignore($this->customer)
            ],

            'password' => $this->isMethod('post') ? [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W)(?!.* ).{8,30}$/',
                // 'confirmed'
            ] : [
                'nullable',
            ],


            // Addresses validation
            'addresses' => ['required', 'array', 'min:1'],

            'addresses.*.address_id' => [
                'required',
                'exists:addresses,id',
                'distinct'
            ],

            'addresses.*.extra_details' => [
                'nullable',
                'string'
            ],

            'addresses.*.is_main' => [
                'required',
                'boolean'
            ],

        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'الاسم الأول',
            'last_name' => 'اسم العائلة',
            'user_name' => 'اسم المستخدم',
            'profile_link' => 'رابط الملف الشخصي',
            'mobile' => 'رقم الجوال',
            'password' => 'كلمة المرور',

            'addresses' => 'العناوين',
            'addresses.*.address_id' => 'العنوان',
            'addresses.*.extra_details' => 'تفاصيل إضافية',
            'addresses.*.is_main' => 'العنوان الرئيسي',
        ];
    }
}
