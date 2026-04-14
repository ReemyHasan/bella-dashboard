<?php

namespace App\Http\Requests\DashUser\Reports;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailedOrdersReportRequest extends FormRequest
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
            'marketer_id' => ['nullable', 'exists:app_users,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'sub_team_id' => ['nullable', 'exists:sub_teams,id'],
            'order_status' => ['nullable', Rule::in(array_column(OrderStatus::cases(), 'value'))],

        ];
    }
}