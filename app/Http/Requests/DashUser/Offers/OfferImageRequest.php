<?php

namespace App\Http\Requests\DashUser\Offers;

use Illuminate\Foundation\Http\FormRequest;

class OfferImageRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1'],

            'images.*.id' => ['nullable', 'exists:offer_images,id'],
            'images.*.file' => ['nullable', 'image', 'max:4096'],
            'images.*.is_main' => ['required', 'boolean'],
            'images.*.sort_order' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $mainCount = collect($this->images)
                ->where('is_main', true)
                ->count();


            if ($mainCount !== 1) {
                $validator->errors()->add(
                    'images',
                    'مسموح فقط صورة رئيسية واحدة'
                );
            }
        });
    }
    public function attributes(): array
    {
        return [
            'images' => "الصور",

            'images.*.id' => "",
            'images.*.file' => "الملف",
            'images.*.is_main' => "رئيسية؟",
            'images.*.sort_order' => "ترتيب الصورة",
        ];
    }
}
