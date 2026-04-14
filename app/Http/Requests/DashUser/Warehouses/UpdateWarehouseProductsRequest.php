<?php

namespace App\Http\Requests\DashUser\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseProductsRequest  extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'required',
                'exists:products,id',
                'distinct'
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:0'
            ],

            // ✅ Deleted items
            'deleted_items' => ['nullable', 'array'],

            'deleted_items.*' => [
                'required',
                'exists:products,id',
                'distinct'
            ],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $items = collect($this->input('items', []))->pluck('product_id');
            $deleted = collect($this->input('deleted_items', []));

            $conflicts = $items->intersect($deleted);

            if ($conflicts->isNotEmpty()) {
                $validator->errors()->add(
                    'deleted_items',
                    'لا يمكن حذف وتحديث نفس المنتج في نفس الطلب.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
           'items' => 'المنتجات',
           'items.*.product_id' => 'المنتج',
           'items.*.quantity' => 'كمية المنتج',
           'deleted_items' => 'المنتجات المحذوفة',
           'deleted_items.*' => 'المنتج المحذوف',

        ];
    }
}
