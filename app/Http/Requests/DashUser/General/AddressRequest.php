<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
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
        $addressId = $this->route('address')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('addresses', 'name')->ignore($addressId),
            ],

            'region_id' => [
                'required',
                'exists:regions,id',
            ],

            // 'delivery_man_id' => [
            //     'nullable',
            //     'exists:app_users,id',
            // ],
            // 'alter_delivery_man_id' => [
            //     'nullable',
            //     'exists:app_users,id',
            // ],


        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'region_id' => 'المنطقة',
            'delivery_man_id' => 'الموزع الأساسي',
            'alter_delivery_man_id' => 'الموزع البديل',

        ];
    }
}
