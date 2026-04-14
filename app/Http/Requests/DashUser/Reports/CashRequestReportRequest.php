<?php

namespace App\Http\Requests\DashUser\Reports;

use App\Enums\CashRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashRequestReportRequest extends FormRequest
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
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', Rule::in(array_column(CashRequestStatus::cases(), 'value'))],
            'delivered_by' => ['nullable', 'string', 'max:255'],
            'team_id' => [
                'nullable',
                'exists:teams,id',
            ],
            'subteam_id' => [
                'nullable',
                'exists:sub_teams,id',
            ],
            'export' => ['nullable', 'in:excel,pdf'],
        ];
    }
}
