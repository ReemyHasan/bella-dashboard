<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
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

            'app_name' => ['nullable', 'string', 'max:255'],
            'offer_general_image' => ['nullable', 'image', 'max:4096'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:255'],

        ];
    }

    public function attributes(): array
    {
        return [
            'app_name' => 'اسم التطبيق',
            'offer_general_image' => 'صورة العرض الرئيسية',
            'support_phone' => 'رقم هاتف الدعم',
            'support_email' => 'بريد الدعم الإلكتروني'
        ];
    }
}
