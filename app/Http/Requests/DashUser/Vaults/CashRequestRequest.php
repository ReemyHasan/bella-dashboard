<?php

namespace App\Http\Requests\DashUser\Vaults;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashRequestRequest extends FormRequest
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

            'from_vault_id' => ['required', 'exists:vaults,id'],

            'requested_amount' => ['required', 'numeric', 'min:1'],

            'requested_for_type' => ['required', 'in:dash_user,app_user'],
            'requested_for_id' => ['required', 'integer'],

            'delivered_by' => [
                'required',
                Rule::exists('app_users', 'id')->where(function ($query) {
                    $query->where('is_delivery_man', true)
                        ->orWhere('is_warehouse_man', true);
                }),
            ],
            'currency_id' => ['required', 'exists:currencies,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'address_id' => [
                'nullable',
                'exists:addresses,id',
                'required_without:address_details'
            ],

            'address_details' => [
                'nullable',
                'string',
                'required_without:address_id'
            ],

            'cash_request_reason' => ['nullable', 'string'],

            'delivery_cost' => ['nullable', 'numeric', 'min:0'],
            // 'additional_delivery_cost' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'from_vault_id' => "من خزنة",

            'requested_amount' => "المبلغ المطلوب",

            'requested_for_type' => "طلب من أجل",
            'requested_for_id' => "طلب من أجل",

            'delivered_by' => "الموصل",

            'currency_id' => "العملة",
            'payment_method_id' => "طريقة الدفع",

            'delivery_cost' => "تكلفة التوصيل",
            'notes' => "ملاحظات",

            'app_user_address_id' => "تفاصيل العنوان",
            'address_details' => "تفاصيل العنوان",
            'cash_request_reason' => "سبب طلب الرصيد",

        ];
    }
}   