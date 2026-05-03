<?php

namespace App\Http\Requests\Mobile\Orders;

use App\Enums\CompetitionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerOrderRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],

            'address_id' => [
                'nullable',
                Rule::exists('customer_addresses', 'address_id')
                    ->where(function ($query) {
                        $query->where('customer_id', $this->customer_id);
                    })
            ],
            'address_details' => ['nullable', 'string'],

            'customer_mobile' => ['required', 'string', 'max:20'],
            'additional_tips' => ['nullable', 'numeric', 'min:0'],


            'adjustment_type' => [
                'nullable',
                'in:percentage,fixed',
                'required_with:adjustment_operation,adjustment_value',
            ],

            'adjustment_operation' => [
                'nullable',
                'in:increase,decrease',
                'required_with:adjustment_type,adjustment_value',
            ],

            'adjustment_value' => [
                'nullable',
                'numeric',
                'min:0',
                'required_with:adjustment_type,adjustment_operation',
            ],
            'notes' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*.product_id' => [
                'required',
                Rule::exists('product_warehouses', 'product_id')
            ],
            'products.*.quantity' => ['required', 'integer', 'min:1'],

            'offers' => ['nullable', 'array'],
            'offers.*.offer_id' => [
                'required',
                Rule::exists('offer_warehouses', 'offer_id')
            ],
            'offers.*.quantity' => ['required', 'integer', 'min:1'],

            'is_target' => [
                'nullable',
                'boolean'
            ],

            'competition_id' => ['nullable'],

        ];
    }


    public function messages(): array
    {
        return [
            'competition_id.exists' => 'المسابقة المختارة غير صالحة أو غير نشطة.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (!$this->address_id && !$this->address_details) {
                $validator->errors()->add('address', 'يجب إدخال عنوان أو تفاصيل العنوان.');
            }
            if ($this->boolean('is_target')) {

                if (!$this->competition_id) {
                    $validator->errors()->add('competition_id', 'يجب اختيار المسابقة.');
                    return;
                }

                $competition = \App\Models\Competition::with([
                    'teams:id',
                    'subteams:id',
                    'marketers:id'
                ])->where('status', CompetitionStatus::active->value)->find($this->competition_id);

                if (!$competition) {
                    return;
                }
                
                if (!$this->isMarketerEligible($competition)) {
                    $validator->errors()->add(
                        'competition_id',
                        'لا يمكنك المشاركة في هذه المسابقة.'
                    );
                }
            }
        });
    }

    public function isMarketerEligible($competition): bool
    {
        $marketerId = auth()->user()->id;
        $team_id = auth()->user()->team_id;
        $sub_team_id = auth()->user()->subteam_id;

        return match ($competition->target) {

            'all' => true,

            'teams' => $competition->teams
                ->pluck('id')
                ->contains($team_id),

            'subteams' => $competition->subteams
                ->pluck('id')
                ->contains($sub_team_id),

            'marketers' => $competition->marketers
                ->pluck('id')
                ->contains($marketerId),

            default => false,
        };
    }
    public function attributes(): array
    {
        return [
            'order_number' => 'رقم الطلب',
            'customer_id' => 'الزبون',
            'address_id' => 'العنوان',
            'address_details' => 'تفاصيل العنوان',
            'customer_mobile' => 'رقم موبايل الزبون',
            'delivery_cost' => 'تكلفة التوصيل',
            'delivery_additional_cost' => 'تكلفة توصيل إضافية',
            'total_base_price' => 'السعر قبل الخصم أو الإضافة',
            'total_price' => 'السعر الكلي',
            'additional_tips' => 'الإضافة',
            'deduction_amount' => 'الخصم',
            'deduction_type' => 'نوع الخصم',
            'notes' => 'ملاحظات',


            'adjustment_type' => 'نوع التعديل',
            'adjustment_operation' => 'إضافة أو خصم',
            'adjustment_value' => 'قيمة الإضافة أو الخصم',


            'products' => 'المنتجات',
            'products.*.product_id' => 'المنتج',
            'products.*.quantity' => 'الكمية',

            // OFFERS
            'offers' => 'العروض',
            'offers.*.offer_id' => 'العرض',
            'offers.*.quantity' => 'الكمية',

            'is_target' => 'تابع لهدف تسويقي',
            'competition_id' => 'الهدف التسويقي'


        ];
    }
}
