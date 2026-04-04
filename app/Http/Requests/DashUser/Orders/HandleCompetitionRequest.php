<?php

namespace App\Http\Requests\DashUser\Orders;

use App\Enums\CompetitionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandleCompetitionRequest extends FormRequest
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
            'status' => ['required', Rule::in(array_column(CompetitionStatus::cases(), 'value'))],

        ];
    }
    public function attributes(): array
    {
        return [
            'status' => 'حالة',
        ];
    }
}
