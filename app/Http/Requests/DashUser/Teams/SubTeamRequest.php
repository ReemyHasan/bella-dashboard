<?php

namespace App\Http\Requests\DashUser\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubTeamRequest extends FormRequest
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
        $subTeamId = $this->route('sub_team');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('sub_teams', 'name')->ignore($subTeamId),
            ],
            'active' => 'required|boolean',
            'is_direct' => 'required|boolean',

            'team_leader_id' => [
                'nullable',
                'exists:app_users,id',
                'prohibited_if:is_direct,1'
            ],

            'team_id' => [
                'required',
                'exists:teams,id',
            ],

            'users' => [
                'nullable',
                'array',
            ],
            'users.*' => [
                function ($attribute, $value, $fail) use ($subTeamId) {

                    if (is_numeric($value)) {

                        $exists = \App\Models\AppUser::where('id', $value)
                            ->where(function ($q) use ($subTeamId) {
                                $q->whereNull('subteam_id')
                                    ->orWhere('subteam_id', $subTeamId);
                            })->first();

                        // dd($subTeamId, $exists );
                        if ($exists == null || ($exists->subteam_id != null && $exists->subteam_id != $subTeamId)) {
                            $fail('المستخدم غير متاح أو منضم لفريق آخر');
                        }
                    } elseif (is_array($value)) {

                        if (
                            empty($value['first_name']) ||
                            empty($value['last_name']) ||
                            empty($value['user_name']) ||
                            empty($value['password']) ||
                            empty($value['mobile'])
                        ) {
                            $fail('بيانات المستخدم غير مكتملة');
                        }
                        // ✅ Check username uniqueness
                        if (\App\Models\AppUser::where('user_name', $value['user_name'])->exists()) {
                            $fail('اسم المستخدم مستخدم بالفعل');
                        }

                        // ✅ Check mobile uniqueness
                        if (\App\Models\AppUser::where('mobile', $value['mobile'])->exists()) {
                            $fail('رقم الجوال مستخدم بالفعل');
                        }
                        if (
                            empty($value['address']) ||
                            !\App\Models\Address::where('id', $value['address'])->exists()
                        ) {
                            $fail('العنوان غير صحيح');
                        }
                    } else {
                        $fail('صيغة المستخدم غير صحيحة');
                    }
                }
            ],
            // 'users.*' => [
            //     'exists:app_users,id',
            //     Rule::exists('app_users', 'id')
            //         ->where(function ($query) use ($subTeamId) {
            //             $query->where(function ($q) {
            //                 $q->whereNull('subteam_id');
            //             })
            //                 ->orWhere('subteam_id', $subTeamId);
            //         }),
            //     function ($attribute, $value, $fail) use ($subTeamId) {

            // $teamId = $this->input('team_id');

            // $user = \App\Models\AppUser::find($value);

            // if (!$user) {
            //     return;
            // }

            // ❌ User belongs to different main team
            // if ($user->team_id != $teamId) {
            //     $fail('العضو المختار منضم لفريق رئيسي مختلف');
            //     return;
            // }

            // ❌ User already belongs to another subteam
            // if ($user->subteam_id && $user->subteam_id != $subTeamId) {
            //     $fail('العضو المختار منضم بالفعل لفريق فرعي آخر');
            // }
            // }
            // ],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($this->boolean('is_direct')) {

                $first = \App\Models\SubTeam::where('team_id', $this->input('team_id'))
                    ->where('is_direct', true)
                    ->first();

                if ($first != null && $first?->id != $this->route('sub_team')) {
                    $validator->errors()->add(
                        'is_direct',
                        'الفريق الرئيسي لديه فعلا فريق مباشر'
                    );
                }
            }
        });
    }
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'active' => 'نشط',
            'is_direct' => 'مباشر',
            'team_leader_id' => 'قائد الفريق',
            'team_id' => 'الفريق',
            'users' => 'أعضاء الفريق',
            'users.*' => 'عضو'

        ];
    }
}
