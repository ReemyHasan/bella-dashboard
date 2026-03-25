<?php

namespace App\Http\Requests\DashUser\Offers;

use Illuminate\Foundation\Http\FormRequest;

class OfferZonePriceSyncRequest extends FormRequest
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
            'zones' => ['required', 'array', 'min:1'],

            'zones.*.zone_id' => [
                'required',
                'exists:zones,id',
                'distinct'
            ],

            'zones.*.price' => ['required', 'numeric', 'min:0'],

            'zones.*.is_available' => ['required', 'boolean'],
        ];
    }


    public function attributes(): array
    {
        return [
            'zones' => "المناطق",

            'zones.*.zone_id' => "المنطقة",

            'zones.*.price' =>"السعر",

            'zones.*.is_available' => "متاحة",
        ];
    }
}
