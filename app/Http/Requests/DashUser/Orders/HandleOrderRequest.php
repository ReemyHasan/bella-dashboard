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
            'reject_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $status = $this->status;

            if (
                in_array($status, ['rejected', 'cancelled']) &&
                empty($this->reject_reason)
            ) {
                $validator->errors()->add('reject_reason', 'سبب الرفض أو الإلغاء مطلوب');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'status' => 'حالة الطلب',
            'reject_reason' => 'سبب الرفض',
            'notes' => 'الملاحظات',


        ];
    }
}