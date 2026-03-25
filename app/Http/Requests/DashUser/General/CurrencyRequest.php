<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
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
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'name')->ignore($this->currency?->id),
            ],
            'symbol' =>  [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies', 'symbol')->ignore($this->currency?->id),
            ],
            'is_main' => 'nullable|boolean',
            'exchange_value' => 'required|numeric',
        ];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'symbol' => 'الرمز',
            'is_main' => 'هل العملة رئيسية',
            'exchange_value' => 'سعر التداول',


        ];
    }
}
