<?php

namespace App\Http\Requests\DashUser\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveWarehouseHandoverRequest extends FormRequest
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
        $handover = $this->route('warehouseHandover');
        // dd($handover);
        return [
            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.id' => [
                'required',
                Rule::exists('warehouse_handover_items', 'id')
                    ->where(function ($query) use ($handover) {
                        $query->where('handover_id', $handover->id);
                    }),
            ],
            'items.*.approved_quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'items.required' => 'يجب إدخال المنتجات.',
            'items.array' => 'صيغة المنتجات غير صحيحة.',
            'items.min' => 'يجب إدخال منتج واحد على الأقل.',

            'items.*.id.required' => 'معرف العنصر مطلوب.',
            'items.*.id.exists' => 'العنصر المحدد لا ينتمي إلى طلب المناقلة الحالي أو غير موجود.',

            'items.*.approved_quantity.required' => 'الكمية الموافق عليها مطلوبة.',
            'items.*.approved_quantity.integer' => 'يجب أن تكون الكمية الموافق عليها رقمًا صحيحًا.',
            'items.*.approved_quantity.min' => 'يجب أن تكون الكمية الموافق عليها أكبر من صفر.',
        ];
    }
    public function attributes(): array
    {
        return [
            'items' => 'المنتجات',
            'items.*.id' => 'العنصر',
            'items.*.approved_quantity' => 'الكمية الموافق عليها',
        ];
    }
}
