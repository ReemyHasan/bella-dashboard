<?php

namespace App\Http\Requests\Mobile\General;

use App\Enums\TargetType;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return ($user->hasRole('Team Manager') || $user->hasRole('Team Leader'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'description' => ['required', 'string', 'max:256'],
            'appears_from' => ['nullable', 'date'],
            'appears_to'   => ['nullable', 'date', 'after_or_equal:appears_from'],

            'target_type'     => ['required', new Enum(TargetType::class)],

            'assignees' => [
                'required',
                'array',
                'min:1'
            ],
        ];
        $table = match ($this->target_type) {
            'team' => 'teams',
            'sub_team' => 'sub_teams',
            'marketer' => 'app_users',
            default => null,
        };

        if ($table) {
            $rules['assignees.*'] = ['integer', Rule::exists($table, 'id')];
        }


        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = auth()->user();

            $targetType = $this->target_type;
            $assignees = $this->assignees ?? [];

            if ($user->hasRole('Team Manager')) {

                foreach ($assignees as $id) {

                    // ✅ Team target
                    if ($targetType === 'team') {

                        $exists = Team::where('id', $id)
                            ->where('id', $user->team_id)
                            ->exists();

                        if (!$exists) {
                            $validator->errors()->add(
                                'assignees',
                                'يمكن لمدير الفريق الإرسال لفريقه فقط'
                            );
                        }
                    }

                    // ✅ SubTeam target
                    if ($targetType === 'sub_team') {

                        $exists = SubTeam::where('id', $id)
                            ->where('team_id', $user->team_id)
                            ->exists();

                        if (!$exists) {
                            $validator->errors()->add(
                                'assignees',
                                'يمكن لمدير الفريق الإرسال فقط للفرق الفرعية التابعة لفريقه'
                            );
                        }
                    }

                    // ✅ Marketers target
                    if ($targetType === 'marketer') {

                        $exists = AppUser::where('id', $id)
                            ->where('team_id', $user->team_id)
                            ->exists();

                        if (!$exists) {
                            $validator->errors()->add(
                                'assignees',
                                'يمكن لمدير الفريق الإرسال فقط للمستخدمين التابعين لفريقه'
                            );
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TEAM LEADER RULES
            |--------------------------------------------------------------------------
            */
            if ($user->hasRole('Team Leader')) {

                foreach ($assignees as $id) {

                    // ❌ Team leader cannot target teams
                    if ($targetType === 'team') {

                        $validator->errors()->add(
                            'target_type',
                            'قائد فريق فرعي لا يمكنه الإرسال إلى فريق رئيسي'
                        );

                        return;
                    }

                    // ✅ SubTeam target
                    if ($targetType === 'sub_team') {

                        $exists = SubTeam::where('id', $id)
                            ->where('id', $user->subteam_id)
                            ->exists();

                        if (!$exists) {
                            $validator->errors()->add(
                                'assignees',
                                'يمكن لقائد الفريق الفرعي الإرسال فقط لفريقه الفرعي'
                            );
                        }
                    }

                    // ✅ Marketers target
                    if ($targetType === 'marketer') {

                        $exists = AppUser::where('id', $id)
                            ->where('subteam_id', $user->subteam_id)
                            ->exists();

                        if (!$exists) {
                            $validator->errors()->add(
                                'assignees',
                                'يمكن لقائد الفريق الإرسال فقط للمستخدمين التابعين لفريقه الفرعي'
                            );
                        }
                    }
                }
            }
        });
    }
    public function attributes(): array
    {
        return [
            'description' => "الوصف",
            'appears_from' => "بدء ظهور الرسالة",
            'appears_to'   => "انتهاء ظهور الرسالة",

            'target_type'     => "نوع المعنيّون بالرسالة",

            'assignees' => "المعنيّون بالرسالة"

        ];
    }
}
