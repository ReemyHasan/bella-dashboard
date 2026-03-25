<?php

namespace App\Http\Requests\DashUser\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseReportRequest extends FormRequest
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

            'warehouse_ids' => ['nullable', 'array'],
            'warehouse_ids.*' => ['exists:warehouses,id'],

            'export' => ['nullable', 'in:excel,pdf'],
        ];
    }
}
