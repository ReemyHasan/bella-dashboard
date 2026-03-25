<?php

namespace App\Http\Requests\DashUser\Users;

use App\Enums\GuardType;
use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class RoleRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role?->id),
            ],
            'name_ar' => [
                'required',
                'string',
                'max:255',
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if (!$this->route('role')) {
            $rules['guard_name'] = ['required', new Enum(GuardType::class)];
        }

        return $rules;
    }


     public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $permissionIds = $this->input('permissions', []);

            if (empty($permissionIds)) {
                return;
            }

            $validPermissions = Permission::whereIn('id', $permissionIds)
                ->where('guard_name', $this->input('guard_name', null))
                ->pluck('id')
                ->toArray();

            if (count($validPermissions) !== count($permissionIds)) {
                $validator->errors()->add(
                    'permissions',
                    'لا يمكنك إسناد بعض الصلاحيات لهذا الدور.'
                );
            }
        });
    }
    
    public function attributes(): array
    {
        return [
            'name' => 'الاسم الإنجليزي',
            'name_ar' => 'الاسم العربي',
            'permissions' => 'الصلاحيات',
            'guard_name' => 'نوع الدور'

        ];
    }
}

