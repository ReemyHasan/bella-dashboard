<?php

namespace App\Http\Requests\DashUser\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubCategoryRequest extends FormRequest
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
        $tagId = $this->route('sub_category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'active' => 'required|boolean',

            'main_category_id' => [
                'required',
                'exists:main_categories,id',
            ],

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'active' => 'نشط',
            'image_path' => 'الصورة',
            'main_category_id' => 'الفئة الرئيسية'
        ];
    }
}
