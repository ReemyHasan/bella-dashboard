<?php

namespace App\Http\Requests\DashUser\General;

use App\Enums\AssignmentType;
use App\Enums\TargetType;
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
        return true;
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

            'assignment_type' => ['required', new Enum(AssignmentType::class)],
            'target_type'     => ['required', new Enum(TargetType::class)],

            'assignees' => [
                'required_if:assignment_type,specific',
                'prohibited_if:assignment_type,all',
                'array'
            ],
        ];

        if ($this->assignment_type === 'specific') {
            $table = match ($this->target_type) {
                'team' => 'teams',
                'sub_team' => 'sub_teams',
                'marketer' => 'app_users',
                default => null,
            };

            if ($table) {
                $rules['assignees.*'] = ['integer', Rule::exists($table, 'id')];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'description' => "الوصف",
            'appears_from' => "بدء ظهور الرسالة",
            'appears_to'   => "انتهاء ظهور الرسالة",

            'assignment_type' => "نوع الإسناد",
            'target_type'     => "نوع المعنيّون بالرسالة",

            'assignees' => "المعنيّون بالرسالة"

        ];
    }
}
