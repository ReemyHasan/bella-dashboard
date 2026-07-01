<?php

namespace App\Http\Requests\Mobile\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // $user = auth()->user();
        // return ($user->hasRole('Team Manager') || $user->hasRole('Team Leader'));
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
            'mobile' => [
                'required',
                'string',
                Rule::unique('customers', 'mobile')
                    ->ignore($this->customer)
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
