<?php

namespace App\Http\Requests\Mobile\AppUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppUserRequestRequest extends FormRequest
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
            'app_user_id' => ['nullable', 'exists:app_users,id'],
            'user_request_type_id' => ['required', 'exists:user_request_types,id'],
            'content' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $user = auth()->user();
        $typeId = $this->user_request_type_id;
        if ($typeId && $typeId != 3) {
            $this->merge([
                'app_user_id' => $user->id,
            ]);
        }
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = auth()->user();

            $typeId = $this->user_request_type_id;
            $targetUserId = $this->app_user_id;

            // ✅ Promotion case
            if ($typeId == 3) {

                if ($targetUserId == $user->id) {
                    $validator->errors()->add(
                        'app_user_id',
                        'لا يمكن تقديم طلب ترقية لنفسك'
                    );
                }

                if (!$user->hasRole('Team Manager')) {
                    $validator->errors()->add(
                        'user_request_type_id',
                        'فقط مدير الفريق يمكنه تقديم طلب ترقية'
                    );
                }
            }
        });
    }
    public function attributes(): array
    {
        return [];
    }
}
