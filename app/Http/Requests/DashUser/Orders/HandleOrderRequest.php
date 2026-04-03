<?php

namespace App\Http\Requests\DashUser\Orders;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandleOrderRequest extends FormRequest
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
            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'cancellation_reason' => ['nullable', 'string'],
            'waiting_reason' => ['nullable', 'string'],
            'waiting_until' => ['nullable', 'date'],

            'notes' => ['nullable', 'string'],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $status = $this->status;

            if (
                in_array($status, ['cancelled']) &&
                empty($this->cancellation_reason)
            ) {
                $validator->errors()->add('cancellation_reason', 'سبب الإلغاء مطلوب');
            }

            if (
                in_array($status, ['waiting']) &&
                empty($this->waiting_reason)
            ) {
                $validator->errors()->add('waiting_reason', 'سبب الانتظار مطلوب');
            }

            if (
                in_array($status, ['waiting']) &&
                empty($this->waiting_until)
            ) {
                $validator->errors()->add('waiting_until', 'مدة الانتظار مطلوبة');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'status' => 'حالة الطلب',
            'cancellation_reason' => 'سبب الإلغاء',
            'waiting_reason' => 'سبب الانتظار',
            'waiting_until' => 'مدة الانتظار',

            'notes' => 'الملاحظات',


        ];
    }
}