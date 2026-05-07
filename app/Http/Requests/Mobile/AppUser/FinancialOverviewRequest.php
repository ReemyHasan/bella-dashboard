<?php

namespace App\Http\Requests\Mobile\AppUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialOverviewRequest extends FormRequest
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

            'to' => [
                'nullable',
                'date',
                'after_or_equal:from'
            ],

            'group_by' => [
                'nullable',
                Rule::in([
                    'daily',
                    'weekly',
                    'monthly'
                ])
            ],

            'scope' => [
                'nullable',
                Rule::in([
                    'mine',
                    'team',
                    'subteam'
                ])
            ],

            'sub_team_id' => [
                'nullable',
                'exists:sub_teams,id'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = auth()->user();

            $scope = $this->scope ?? 'mine';

            // 🔹 Normal marketer restrictions
            if (
                !$user->hasRole('Team Manager') &&
                !$user->hasRole('Team Leader')
            ) {

                if ($scope !== 'mine') {

                    $validator->errors()->add(
                        'scope',
                        'غير مصرح لك باستخدام هذا النطاق'
                    );
                }
            }

            if ($user->hasRole('Team Leader')) {

                if ($scope === 'team') {

                    $validator->errors()->add(
                        'scope',
                        'مدير الفريق الفرعي لا يمكنه عرض بيانات الفريق بالكامل'
                    );
                }

                if (
                    $scope === 'subteam' &&
                    $this->filled('sub_team_id') &&
                    (int) $this->sub_team_id !== (int) $user->subteam_id
                ) {

                    $validator->errors()->add(
                        'sub_team_id',
                        'يمكنك فقط عرض بيانات فريقك الفرعي'
                    );
                }
            }

            if ($user->hasRole('Team Manager')) {

                if (
                    $scope === 'subteam' &&
                    $this->filled('sub_team_id')
                ) {

                    $exists = \App\Models\SubTeam::query()
                        ->where('id', $this->sub_team_id)
                        ->where('team_id', $user->team_id)
                        ->exists();

                    if (!$exists) {

                        $validator->errors()->add(
                            'sub_team_id',
                            'الفريق الفرعي لا يتبع لفريقك'
                        );
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [

            'from' => 'من تاريخ',
            'to' => 'إلى تاريخ',
            'group_by' => 'نوع التجميع',
            'scope' => 'نطاق البيانات',
            'sub_team_id' => 'الفريق الفرعي',
        ];
    }
}
