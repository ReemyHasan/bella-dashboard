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
            'warehouses' => ['required', 'array', 'min:1'],

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
            'warehouses' => "المستودعات",

            'warehouses.*.warehouse_id' => "المستودع",

            'warehouses.*.quantity' =>"الكمية",
        ];
    }
}
