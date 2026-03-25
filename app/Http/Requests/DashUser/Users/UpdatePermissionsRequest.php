<?php

namespace App\Http\Requests\DashUser\Users;

use App\Enums\GuardType;
use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePermissionsRequest extends FormRequest
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
            'permissions'   => 'nullable|array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $permissionIds = $this->input('permissions', []);

            if (empty($permissionIds)) {
                return;
            }

            // Get all valid permissions for this guard in ONE query
            $validPermissions = Permission::whereIn('id', $permissionIds)
                ->where('guard_name', GuardType::dash_user_guard->value)
                ->pluck('id')
                ->toArray();

            // Compare counts
            if (count($validPermissions) !== count($permissionIds)) {
                $validator->errors()->add(
                    'permissions',
                    'لا يمكنك إسناد بعض الصلاحيات لمستخدم لوحة التحكم.'
                );
            }
        });
    }
}
