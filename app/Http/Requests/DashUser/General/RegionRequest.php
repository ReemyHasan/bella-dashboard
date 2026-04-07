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

            'delivery_cost' => 'required|numeric|min:0',

            'symbol' => [
                'required',
                'string',
                'max:10',
                // Rule::unique('regions', 'symbol')->ignore($regionId),
            ],

            'zone_id' => [
                'required',
                'exists:zones,id',
            ],
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) {
                    if ($this->zone_id) {
                        $query->where('zone_id', $this->zone_id);
                    }
                }),
            ],
            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(function ($query) {
                    $query->where('active', true);

                    if ($this->zone_id) {
                        $query->where('zone_id', $this->zone_id);
                    }
                }),
            ],

            'addresses' => 'nullable|array|min:0',

            'addresses.*.id' => [
                'nullable',
                Rule::exists('addresses', 'id')
                    ->where(fn($q) => $q->where('region_id', $regionId)),
            ],
            'addresses.*.name' => [
                'required',
                'string',
                'max:255',
            ],
            'addresses_removed' => [
                'nullable',
                'array'
            ],
            'addresses_removed.*' => [
                Rule::exists('addresses', 'id')
                    ->where(fn($q) => $q->where('region_id', $regionId)),
            ],


        ];
    }

    public function messages(): array
    {
        return [
            'city_id.exists' => 'المدينة المختارة لا تتبع للمنطقة المحددة.',
            'warehouse_id.exists' => 'المستودع يجب أن يكون نشط ويتبع لنفس المنطقة المحددة.',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'symbol' => 'الرمز',
            'city_id' => 'المدينة',
            'zone_id' => 'الموقع الجغرافي',
            'delivery_cost' => 'تكلفة التوصيل',
            'warehouse_id' => 'المستودع',


            'addresses' => 'العناوين',
            'addresses.*.name' => 'اسم العنوان',
            'addresses.*.id' => 'العنوان',
            'addresses_removed' => 'العناوين المراد حذفها',
            'addresses_removed.*' => 'العنوان المراد حذفه',


        ];
    }
}
