<?php

namespace App\Http\Requests\DashUser\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
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
        $brandId = $this->route('brand')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'active' => 'required|boolean'

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'active' => 'نشط',
            'image_path' => 'الصورة'
        ];
    }
}
