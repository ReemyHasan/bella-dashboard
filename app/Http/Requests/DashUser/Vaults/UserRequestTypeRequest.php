<?php

namespace App\Http\Requests\DashUser\Vaults;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequestTypeRequest extends FormRequest
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
        $typeId = $this->route('user_request_type')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_request_types', 'name')->ignore($typeId),
            ],

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم'
        ];
    }
}
