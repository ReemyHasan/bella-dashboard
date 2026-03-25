<?php

namespace App\Http\Requests\DashUser\Offers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
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
        $offerId = $this->route('offer')?->id;

        return [

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('offers', 'name')->ignore($offerId),
            ],
            'symbol' => [
                'required',
                'string',
                'max:255',
                Rule::unique('offers', 'symbol')->ignore($offerId),
            ],
            'description' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'marketing_description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],

            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],

            //Products
            'products' => ['required', 'array', 'min:1'],

            'products.*.product_id' => [
                'required',
                'exists:products,id',
                'distinct'
            ],

            'products.*.quantity' => ['required', 'numeric', 'min:0'],


            'warehouses' => ['nullable', 'array', 'min:1'],

            'warehouses.*.warehouse_id' => [
                'required',
                'exists:warehouses,id',
                'distinct'
            ],

            'warehouses.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [

            'name' => "الاسم",
            'symbol' => "الرمز",

            'description' => "الوصف",
            'summary' => "الملخص",
            'marketing_description' => "الوصف التسويقي",

            'active' => "فعّال",

            // Tags
            'tags' => "الوسوم",
            'tags.*' => "الوسم",

            'products' => "المنتجات",

            'products.*.product_id' => "المنتج",

            'products.*.quantity' => "الكمية",

            'warehouses' => "المستودعات",

            'warehouses.*.warehouse_id' => "المستودع",

            'warehouses.*.quantity' => "الكمية",
        ];
    }
}
