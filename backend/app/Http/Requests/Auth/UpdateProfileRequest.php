<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'national_id' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'national_id')->ignore($userId),
            ],
        ];
    }

    /**
     * Custom validation messages in Arabic.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفاً.',
            'phone.max' => 'يجب ألا يتجاوز رقم الهاتف 20 رقماً.',
            'national_id.unique' => 'رقم الهوية الوطنية مسجل مسبقاً لمستخدم آخر.',
            'national_id.max' => 'يجب ألا يتجاوز رقم الهوية 20 حرفاً أو رقماً.',
        ];
    }
}
