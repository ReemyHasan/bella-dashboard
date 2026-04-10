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
                    $query->where('is_warehouse_man', true);
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $deliveredBy = $this->input('delivered_by');
            $vaultId = $this->input('from_vault_id');
            if (!$deliveredBy || !$vaultId) {
                return;
            }

            $vaultExists = \App\Models\Vault::where('id', $vaultId)
                ->where('owner_id', $deliveredBy)
                ->exists();

            if (!$vaultExists) {
                $validator->errors()->add(
                    'from_vault_id',
                    'الخزنة المختارة لا تتبع لهذا الموصل.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'from_vault_id.required' => 'يجب اختيار الخزنة.',
            'from_vault_id.exists' => 'الخزنة غير موجودة.',

            'requested_amount.required' => 'المبلغ مطلوب.',
            'requested_amount.numeric' => 'المبلغ يجب أن يكون رقم.',
            'requested_amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',

            'requested_for_type.required' => 'يجب تحديد نوع المستلم.',
            'requested_for_type.in' => 'نوع المستلم غير صحيح.',

            'requested_for_id.required' => 'يجب تحديد المستلم.',
            'requested_for_id.integer' => 'المستلم غير صالح.',

            'delivered_by.required' => 'يجب اختيار الموصل.',
            'delivered_by.exists' => 'الموصل غير موجود أو ليس مندوب مستودع.',

            'currency_id.required' => 'يجب اختيار العملة.',
            'currency_id.exists' => 'العملة غير موجودة.',

            'payment_method_id.required' => 'يجب اختيار طريقة الدفع.',
            'payment_method_id.exists' => 'طريقة الدفع غير موجودة.',

            'address_id.required_without' => 'يجب إدخال عنوان أو تفاصيل العنوان.',
            'address_details.required_without' => 'يجب إدخال عنوان أو تفاصيل العنوان.',

            'delivery_cost.numeric' => 'تكلفة التوصيل يجب أن تكون رقم.',
            'delivery_cost.min' => 'تكلفة التوصيل لا يمكن أن تكون سالبة.',
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
