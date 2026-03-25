<?php

namespace App\Http\Requests\DashUser\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionRequest extends FormRequest
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
        $regionId = $this->route('region')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('regions', 'name')->ignore($regionId),
            ],

            'symbol' => [
                'required',
                'string',
                'max:10',
                // Rule::unique('regions', 'symbol')->ignore($regionId),
            ],

            'city_id' => [
                'required',
                'exists:cities,id',
            ],
            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(function ($query) {
                    $query->where('active', true);
                }),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'symbol' => 'الرمز',
            'city_id' => 'المدينة',
            'warehouse_id' => 'المستودع'
        ];
    }
}
