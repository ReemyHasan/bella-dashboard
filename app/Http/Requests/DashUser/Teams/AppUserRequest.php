<?php

namespace App\Http\Requests\DashUser\Teams;

use App\Enums\DashUserStatus;
use App\Enums\GuardType;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class AppUserRequest extends FormRequest
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
        $userId = $this->route('app_user')?->id;

        return [
            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'user_name'     => ['required', 'string', 'max:255', Rule::unique('app_users', 'user_name')->ignore($userId)],
            'profile_link'     => 'nullable|string|max:255|url',
            'mobile'  => ['required', 'string', 'max:20', Rule::unique('app_users', 'mobile')->ignore($userId)],

            'password' => $this->isMethod('post') ? [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\W)(?!.* ).{8,30}$/',
                // 'confirmed'
            ] : [
                'nullable',
            ],
            'birth_date'         => 'required|date',
            'join_date'         => 'required|date',
            'status'        => ['required', new Enum(DashUserStatus::class)],

            // 'is_team_leader' => 'nullable|boolean',
            // 'is_manager' => 'nullable|boolean',
            // 'is_delivery_man' => 'nullable|boolean',
            // 'is_warehouse_man' => 'nullable|boolean',
            'team_id' => [
                'nullable',
                'exists:teams,id',
            ],
            'subteam_id' => [
                'nullable',
                Rule::exists('sub_teams', 'id')
                    ->where(function ($query) {
                        $query->where('team_id', $this->team_id);
                    }),
            ],

            'warehouse_id' => [
                'nullable',
                'exists:warehouses,id',
            ],
            'addresses' => 'nullable|array|min:0',

            'addresses.*.id' => [
                'required',
                'exists:addresses,id',
                'distinct'
            ],

            'addresses.*.is_main' => [
                'required',
                'boolean',
            ],
            'balance' => 'required|numeric',

            // 'roles' => $this->isMethod('post') ? [
            //     'required',
            //     'array',
            //     'min:1'
            // ] : [
            //     'nullable',
            //     'array'
            // ],

            // 'roles.*' => [
            //     'required',
            //     'integer',
            //     'exists:roles,id'
            // ],
        ];
    }

    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {

        //     $addresses = $this->input('addresses', []);

        //     $mainCount = collect($addresses)
        //         ->where('is_main', true)
        //         ->count();

        //     if ($mainCount !== 1) {
        //         $validator->errors()->add(
        //             'addresses',
        //             'لا يمكنك إضافة أكثر من عنوان رئيسي'
        //         );
        //     }
        // });

        // $validator->after(function ($validator) {

        //     $roleIds = $this->input('roles', []);

        //     if (empty($roleIds)) {
        //         return;
        //     }

        //     $validRoles = Role::whereIn('id', $roleIds)
        //         ->where('guard_name', GuardType::app_user_guard->value)
        //         ->pluck('id')
        //         ->toArray();
        //     if (count($validRoles) !== count($roleIds)) {
        //         $validator->errors()->add(
        //             'roles',
        //             'لا يمكنك إسناد بعض الأدوار لمستخدم التطبيق.'
        //         );
        //     }
        // });
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
            'join_date'         => 'تاريخ الانضمام',
            'is_team_leader' => 'قائد فريق',
            'is_manager' => 'مدير',
            'is_delivery_man' => 'موزع',
            'is_warehouse_man' => 'أمين مستودع',
            'team_id' => 'الفريق',
            'subteam_id' => 'الفريق الفرعي',
            'warehouse_id' => 'المستودع',
            'addresses' => 'العناوين',
            'addresses.*.id' => 'العنوان',
            'addresses.*.is_main' => 'هل هو رئيسي',
            'balance' => 'الرصيد'

        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة وأرقام ورمز خاص.',
        ];
    }
}
