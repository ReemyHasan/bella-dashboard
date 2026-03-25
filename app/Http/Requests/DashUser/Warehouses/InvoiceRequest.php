<?php

namespace App\Http\Requests\DashUser\Warehouses;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
        $invoiceId = $this->route('invoice')?->id;
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'name_of_merchant' => [
                'required',
                'string',
                'max:255',
            ],

            'date' => [
                'required',
                'date',
            ],
            'is_confirmed' => 'required|boolean',

            'warehouse_id' => [
                'required',
                'exists:warehouses,id',
            ],

            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'products.*.added_quantity' => ['required', 'integer', 'min:1'],

        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'العنوان',
            'date' => 'تاريخ الفاتورة',
            'is_confirmed' => 'تأكيد الفاتورة',
            'name_of_merchant' => 'اسم التاجر',
            'warehouse_idd' => 'المستودع',

            'products' => "المنتجات",
            'products.*.product_id' => "المنتج",
            'products.*.added_quantity' => "الكمية المضافة",
        ];
    }
}
