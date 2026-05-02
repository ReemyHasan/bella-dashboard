<?php

namespace App\Http\Requests\DashUser\Orders;

use App\Enums\CompetitionTarget;
use App\Enums\CompetitionType;
use App\Models\AppUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // 🔹 Basic Info
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],

            'prize' => ['nullable', 'numeric', 'min:0'],

            // 🔹 Type
            'type' => [
                'required',
                Rule::in(array_column(CompetitionType::cases(), 'value')),
            ],

            'target_value' => ['nullable', 'numeric', 'min:0'],

            // 🔹 Target (who participates)
            'target' => [
                'required',
                Rule::in(array_column(CompetitionTarget::cases(), 'value')),
            ],
            'co_created_by_id' => ['nullable', 'exists:app_users,id'],

            // 🔹 Target Entities
            'teams' => ['nullable', 'array'],
            'teams.*' => ['exists:teams,id'],

            'subteams' => ['nullable', 'array'],
            'subteams.*' => ['exists:sub_teams,id'],

            'marketers' => ['nullable', 'array'],
            'marketers.*' => ['exists:app_users,id'],

            // 🔹 Zones
            'zones' => ['required', 'array', 'min:1'],
            'zones.*' => ['exists:zones,id'],

            // 🔹 Product-based competition
            'products' => ['nullable', 'array'],
            'products.*.product_id' => ['required_with:products', 'exists:products,id'],
            'products.*.target_quantity' => ['required_with:products', 'integer', 'min:1'],

            // 🔹 Offer-based competition
            'offers' => ['nullable', 'array'],
            'offers.*.offer_id' => ['required_with:offers', 'exists:offers,id'],
            'offers.*.target_quantity' => ['required_with:offers', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // 🔹 Type validation
            if (
                in_array($this->type, ['financial_amount', 'orders_count', 'general_product_sales'])
                && empty($this->target_value)
            ) {

                $validator->errors()->add(
                    'target_value',
                    'قيمة الهدف مطلوبة لهذا النوع من المسابقة.'
                );
            }

            if ($this->type == 'product_sales' && empty($this->products)) {
                $validator->errors()->add('products', 'يجب تحديد المنتجات.');
            }

            if ($this->type == 'offer_sales' && empty($this->offers)) {
                $validator->errors()->add('offers', 'يجب تحديد العروض.');
            }

            $target = $this->input('target');
            $coCreatorId = $this->input('co_created_by_id');

            // ✅ Case 1: No co_creator → everything allowed
            if (!is_null($coCreatorId)) {
                $coCreator = AppUser::findOrFail($coCreatorId);
                // 🔹 TEAM MANAGER
                if ($coCreator->hasRole('Team Manager')) {

                    if (!in_array($target, [CompetitionTarget::subteams->value, CompetitionTarget::marketers->value])) {
                        $validator->errors()->add('target', 'يمكن لمدير الفريق فقط اختيار فرق فرعية أو مسوقين تابعين للفرق المشرف عليها.');
                       
                    }

                    // Validate subteams belong to his team
                    if ($target == CompetitionTarget::subteams->value) {
                        $invalid = \App\Models\SubTeam::whereIn('id', $this->subteams ?? [])
                            ->where('team_id', '!=', $coCreator->team_id)
                            ->exists();

                        if ($invalid) {
                            $validator->errors()->add('subteams', 'بعض الفرق الفرعية المختارة غير تابع للفريق الخاص بالمدير.');
                        }
                    }

                    // Validate marketers belong to his team or its subteams
                    if ($target == CompetitionTarget::marketers->value) {
                        $invalid = AppUser::whereIn('id', $this->marketers ?? [])
                            ->where(function ($q) use ($coCreator) {
                                $q->where('team_id', '!=', $coCreator->team_id);
                            })
                            ->exists();

                        if ($invalid) {
                            $validator->errors()->add('marketers', 'بعض المسوقين غير تابعين للفريق أو أحد الفرق الفرعية المشرف عليها.');
                        }
                    }
                }

                // 🔹 TEAM LEADER
                if ($coCreator->hasRole('Team Leader')) {

                    if ($target != CompetitionTarget::marketers->value) {
                        $validator->errors()->add('target', 'يمكن لدير الفريق الفرعي فقط اختيار مسوقين تابعين له.');
                      
                    }

                    // Validate marketers belong to his subteam
                    $invalid = AppUser::whereIn('id', $this->marketers ?? [])
                        ->where('subteam_id', '!=', $coCreator->subteam_id)
                        ->exists();

                    if ($invalid) {
                        $validator->errors()->add('marketers', 'بعض المسوقين غير تابعين للفريق المشرف عليه قائد الفريق.');
                    }
                }
            }

            // 🔹 Target validation
            if ($this->target == 'teams' && empty($this->teams)) {
                $validator->errors()->add('teams', 'يجب اختيار الفرق.');
            }

            if ($this->target == 'subteams' && empty($this->subteams)) {
                $validator->errors()->add('subteams', 'يجب اختيار الفرق الفرعية.');
            }

            if ($this->target == 'marketers' && empty($this->marketers)) {
                $validator->errors()->add('marketers', 'يجب اختيار المسوقين.');
            }
        });
    }
    public function messages(): array
    {
        return [
            'zones.required' => 'يجب اختيار منطقة واحدة على الأقل.',
            'zones.*.exists' => 'إحدى المناطق غير موجودة.',

            'type.in' => 'نوع المسابقة غير صالح.',
            'target.in' => 'نوع الاستهداف غير صالح.',

            'end_at.after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'اسم المسابقة',
            'description' => 'الوصف',
            'start_at' => 'تاريخ البداية',
            'end_at' => 'تاريخ النهاية',
            'prize' => 'الجائزة',

            'type' => 'نوع المسابقة',
            'target_value' => 'قيمة الهدف',

            'target' => 'نوع الاستهداف',

            'zones' => 'المناطق',

            'teams' => 'الفرق',
            'subteams' => 'الفرق الفرعية',
            'marketers' => 'المسوقين',

            'products' => 'المنتجات',
            'offers' => 'العروض',
        ];
    }
}
