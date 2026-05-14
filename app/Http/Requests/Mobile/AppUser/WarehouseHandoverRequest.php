<?php

namespace App\Http\Requests\Mobile\AppUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseHandoverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->hasRole('Warehouse Keeper');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $handover = $this->route('warehouse_handover');

        return [
            'provider_warehouse_id' => [
                Rule::requiredIf(!$handover),
                'exists:warehouses,id'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:500'
            ],

            'products' => [
                'required',
                'array',
                'min:1'
            ],

            'products.*.product_id' => [
                'required',
                'exists:products,id'
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'requester_warehouse_id' => 'المستودع الطالب',
            'provider_warehouse_id' => 'المستودع المزوّد',
            'notes' => 'الملاحظات',

            'products' => 'المنتجات',
            'products.*.product_id' => 'المنتج',
            'products.*.quantity' => 'الكمية المطلوبة',
        ];
    }
}
