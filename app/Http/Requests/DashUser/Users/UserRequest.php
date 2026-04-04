<?php

namespace App\Http\Requests\DashUser\Users;

use App\Enums\DashUserStatus;
use App\Enums\GuardType;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [



            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'user_name'     => 'required|string|max:255',
            'profile_link'     => 'nullable|string|max:255|url',
            'balance' => 'nullable|numeric',

            'password' => $this->isMethod('post') ? [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W)(?!.* ).{8,30}$/',
                // 'confirmed'
            ] : [
                'nullable',
            ],
            'mobile'  => ['required', 'string', 'max:20', Rule::unique('dash_users', 'mobile')->ignore($userId)],
            'birth_date'         => 'required|date',
            'roles' => $this->isMethod('post') ? [
                'required',
                'array',
                'min:1'
            ] : [
                'nullable',
                'array'
            ],

            'roles.*' => [
                'required',
                'integer',
                'exists:roles,id'
            ],
            'status'        => ['required', new Enum(DashUserStatus::class)],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $roleIds = $this->input('roles', []);

            if (empty($roleIds)) {
                return;
            }

            $validRoles = Role::whereIn('id', $roleIds)
                ->where('guard_name', GuardType::dash_user_guard->value)
                ->pluck('id')
                ->toArray();
            if (count($validRoles) !== count($roleIds)) {
                $validator->errors()->add(
                    'roles',
                    'لا يمكنك إسناد بعض الأدوار لمستخدم لوحة التحكم.'
                );
            }
        });
    }

    /**
     * Translate field names to Arabic
     */
    public function attributes(): array
    {
        return [
            'first_name'   => 'الاسم الأول',
            'last_name'    => 'اسم العائلة',
            'user_name'    => 'اسم المستخدم',
            'profile_link' => 'رابط الملف الشخصي',
            'password'     => 'كلمة المرور',
            'mobile'       => 'رقم الجوال',
            'birth_date'   => 'تاريخ الميلاد',
            'roles'        => 'الأدوار',
            'roles.*'      => 'الدور',
            'status'       => 'الحالة',
            'balance'      => 'الرصيد'
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة وأرقام ورمز خاص.',
        ];
    }
}
