<?php

namespace App\Http\Requests\DashUser\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product')?->id;

        $rules =  [
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'sub_category_id'  => ['nullable', Rule::exists('sub_categories', 'id')
                ->where(function ($query) {
                    $query->where('main_category_id', $this->main_category_id);
                })],

            'brand_id' => ['nullable', 'exists:brands,id'],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],

            'size' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'string'],
            'how_to_use' => ['nullable', 'string'],
            'precautions' => ['nullable', 'string'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],

            'active' => ['required', 'boolean'],

            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
        if ($this->isMethod('post')) {
            $rules['warehouses'] = ['nullable', 'array'];

            $rules['warehouses.*.warehouse_id'] = [
                'required',
                'exists:warehouses,id',
                'distinct'
            ];

            $rules['warehouses.*.quantity'] = [
                'required',
                'integer',
                'min:0'
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'main_category_id' => "الفئة الرئيسية",
            'sub_category_id'  => "الفئة الفرعية",

            'name' => "الاسم",
            'size' => "الحجم",

            'description' => "الوصف",
            'how_to_use' => "طريقة الاستخدام",
            'precautions' => "المحاذير",
            'country_of_origin' => "المنشأ",

            'active' => "فعّال",

            // Tags
            'tags' => "الوسوم",
            'tags.*' => "الوسم",
        ];
    }
}
