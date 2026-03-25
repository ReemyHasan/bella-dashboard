<?php

namespace App\Http\Requests\DashUser\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectWarehouseHandoverRequest extends FormRequest
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
            'reason' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' =>"سبب الرفض"
        ];
    }
}
