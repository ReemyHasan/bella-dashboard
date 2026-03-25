<?php

namespace App\Http\Requests\DashUser\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
        $warehouseId = $this->route('warehouse')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')->ignore($warehouseId),
            ],

            'active' => 'required|boolean',
            'is_main' => 'required|boolean',


            'zone_id' => [
                'required',
                'exists:zones,id',

            ],

            'keeper_id' => [
                'nullable',
                'exists:app_users,id',
                Rule::unique('warehouses', 'keeper_id')->ignore($warehouseId)

            ],

        ];
    }

    public function messages(): array
    {
        return [
            'keeper_id.unique' => 'هذا المستخدم مُعيَّن بالفعل كأمين لمستودع آخر ولا يمكن تعيينه لأكثر من مستودع.'
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'active' => 'نشط',
            'is_main' => 'مستودع رئيسي',
            'zone_id' => 'المنطقة الجغرافية',
            'keeper_id' => 'أمين المستودع'
        ];
    }
}
