<?php

namespace App\Http\Requests\Mobile\AppUser;

use App\Models\AppUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseReviewRequest extends FormRequest
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
            'reviewed_user_id' => ['required', 'exists:app_users,id'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        // optional: enforce reviewer is current user
        $this->merge([
            'reviewer_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $reviewer = auth()->user();
            $reviewed = AppUser::find($this->reviewed_user_id);

            if (!$reviewed) {
                return;
            }

            // ❌ Reviewer cannot be warehouse man
            if ($reviewer->is_warehouse_man) {
                $validator->errors()->add(
                    'reviewer',
                    'الموزع لا يمكنه تقييم الآخرين'
                );
            }

            // ❌ Reviewed must be warehouse man
            if (!$reviewed->is_warehouse_man) {
                $validator->errors()->add(
                    'reviewed_user_id',
                    'يمكن تقييم الموزعبن فقط'
                );
            }

            // ❌ Prevent self-review (important)
            if ($reviewer->id === $reviewed->id) {
                $validator->errors()->add(
                    'reviewed_user_id',
                    'لا يمكنك تقييم نفسك'
                );
            }
        });
    }
    public function attributes(): array
    {
        return [];
    }
}
