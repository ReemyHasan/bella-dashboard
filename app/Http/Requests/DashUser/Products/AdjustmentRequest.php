<?php

namespace App\Http\Requests\DashUser\Products;

use Illuminate\Foundation\Http\FormRequest;

class AdjustmentRequest extends FormRequest
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
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
            'type' => ['required', 'in:percentage,fixed'],
            'operation' => ['required', 'in:increase,decrease'],
            'value' => ['required', 'numeric', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [
            'type.required' => 'نوع التعديل مطلوب.',
            'type.in' => 'نوع التعديل يجب أن يكون نسبة أو قيمة ثابتة.',

            'operation.required' => 'نوع العملية مطلوب.',
            'operation.in' => 'العملية يجب أن تكون زيادة أو نقصان.',

            'value.required' => 'قيمة التعديل مطلوبة.',
            'value.numeric' => 'قيمة التعديل يجب أن تكون رقم.',

            'product_ids.*.exists' => 'أحد المنتجات غير موجود.',
        ];
    }
    public function attributes(): array
    {
        return [
            'product_ids' => 'المنتجات',
            'product_ids.*' => 'المنتج',
            'type' => 'نوع الطلب',
            'operation' => 'العملية',
            'value' => 'القيمة',
        ];
    }
}
