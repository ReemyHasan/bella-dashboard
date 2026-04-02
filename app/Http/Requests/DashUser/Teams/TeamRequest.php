<?php

namespace App\Http\Requests\DashUser\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamRequest extends FormRequest
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
        $teamId = $this->route('team')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->ignore($teamId),
            ],
            'active' => 'required|boolean',
            'manager_id' => [
                'nullable',
                'exists:app_users,id',
            ],

            'sub_teams' => 'required|array|min:1',

            'sub_teams.*.id' => [
                'nullable',
                Rule::exists('sub_teams', 'id')
                    ->where(fn($q) => $q->where('team_id', $this->route('team')?->id)),
            ],
            'sub_teams.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'sub_teams.*.active' => [
                'required',
                'boolean',
            ],
            'sub_teams.*.is_direct' => [
                'required',
                'boolean',
            ],
            'sub_teams.*.team_leader_id' => [
                'nullable',
                'exists:app_users,id',
                'prohibited_if:sub_teams.*.is_direct,1'
            ],
            'sub_teams_removed' => [
                'nullable',
                'array'
            ],
            'sub_teams_removed.*' => [
                Rule::exists('sub_teams', 'id')
                    ->where(fn($q) => $q->where('team_id', $this->route('team')?->id)),
            ],

            'marketer_percentage' => 'required|numeric|min:0|max:100',
            'team_leader_percentage' => 'required|numeric|min:0|max:100',
            'manager_percentage' => 'required|numeric|min:0|max:100',
            'direct_manager_percentage' => 'required|numeric|min:0|max:100',
            // 'delivery_man_percentage' => 'required|numeric|min:0|max:100',
            // 'warehouse_man_percentage' => 'required|numeric|min:0|max:100',


        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $subTeams = $this->input('sub_teams', []);

            $directCount = 0;

            foreach ($subTeams as $index => $subTeam) {

                $isDirect = data_get($subTeam, 'is_direct');
                $leader   = data_get($subTeam, 'team_leader_id');

                if ($isDirect) {
                    $directCount++;

                    if ($leader) {
                        $validator->errors()->add(
                            "sub_teams.$index.team_leader_id",
                            'لا يمكن إضافة مدير فريق لفريق مباشر'
                        );
                    }
                }
            }

            if ($directCount > 1) {
                $validator->errors()->add(
                    'sub_teams',
                    'الفريق الرئيسي لا يمكن أن يكون لديه أكثر من فريق مباشر'
                );
            }
        });

        $validator->after(function ($validator) {
            $total =
                $this->marketer_percentage +
                $this->team_leader_percentage +
                $this->manager_percentage +

                // $this->delivery_man_percentage +
                // $this->warehouse_man_percentage +

                $this->direct_manager_percentage;

            if ($total > 100) {
                $validator->errors()->add('marketer_percentage', 'مجموع النسب يتجاوز ال100%.');
            }
        });
    }
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'active' => 'نشط',
            'manager_id' => 'المدير',
            'sub_teams' => 'الفرق الفرعية',
            'sub_teams.*.name' => 'اسم الفريق الفرعي',
            'sub_teams.*.active' => 'حالة الفريق الفرعي',
            'sub_teams.*.team_leader_id' => 'قائد الفريق الفرعي',
            'sub_teams.*.is_direct' => 'هل الفريق مباشر',
            'marketer_percentage' => 'نسبة المسوق',
            'team_leader_percentage' => 'نسبة مدير الفريق',
            'manager_percentage' => 'نسبة المدير',
            'direct_manager_percentage' => 'نسبة المدير المباشر',
            'delivery_man_percentage' => 'نسبة الموزع',
            'warehouse_man_percentage' => 'نسبة أمين المستودع',
        ];
    }
}
