<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ZoneRequest extends FormRequest
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
        $zoneId = $this->route('zone')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('zones', 'name')->ignore($zoneId),
            ],

            'symbol' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('zones', 'symbol')->ignore($zoneId),
            ],

            'currency_id' => [
                'required',
                'exists:currencies,id',
            ],

            

            'tips' => 'nullable|array',
            'tips.*.amount' => 'required_with:tips|numeric|min:0',
        ];
    }
    public function withValidator($validator)
    {
       
    }
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'symbol' => 'الرمز',
            'currency_id' => 'العملة',
            
            'tips' => 'العمولة',
            "tips.*" => 'العمولة'
        ];
    }
}
