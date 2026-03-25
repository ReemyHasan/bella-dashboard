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
        return [
            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.id' => [
                'required',
                'exists:warehouse_handover_items,id'
            ],

            'items.*.approved_quantity' => [
                'required',
                'integer',
                'min:1'
            ],
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
