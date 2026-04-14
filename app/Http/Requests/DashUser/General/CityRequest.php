<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
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
        $cityId = $this->route('city')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name')->ignore($cityId),
            ],

            'symbol' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('cities', 'symbol')->ignore($cityId),
            ],

            'zone_id' => [
                'required',
                'exists:zones,id',
            ],

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'symbol' => 'الرمز',
            'zone_id' => 'المنطقة الجغرافية',
        ];
    }
}
