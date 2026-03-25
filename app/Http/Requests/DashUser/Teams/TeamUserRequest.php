<?php

namespace App\Http\Requests\DashUser\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamUserRequest extends FormRequest
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
            'users' => [
                'required',
                'array',
            ],

            'users.*' => [
                'exists:app_users,id',
                Rule::exists('app_users', 'id')
                    ->where(function ($query) use ($teamId) {
                        $query->where(function ($q) {
                            $q->whereNull('team_id')->whereNull('subteam_id');
                        })
                            ->orWhere('team_id', $teamId);
                    }),
            ],
        ];
    }

    /**
     * Ensure team has a direct subteam
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $team = $this->route('team');

            if (!$team) {
                return;
            }

            $hasDirectSubTeam = $team->subTeams()
                ->where('is_direct', true)
                ->exists();

            if (!$hasDirectSubTeam) {
                $validator->errors()->add(
                    'team',
                    'الفريق يجب أن يحتوي على فريق مباشر.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'users' => 'أعضاء الفريق',
            'users.*' => 'عضو'
        ];
    }
}
