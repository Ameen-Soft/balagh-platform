<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;

class TransferComplaintRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'to_department_id' => ['required', 'integer', 'exists:departments,id'],
            'to_ministry_id' => ['nullable', 'integer', 'exists:ministries,id'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
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
            'to_department_id.required' => 'الإدارة المحول إليها مطلوبة.',
            'to_department_id.exists' => 'الإدارة المحددة غير موجودة في النظام.',
            'to_ministry_id.exists' => 'الوزارة المحددة غير موجودة في النظام.',
            'reason.required' => 'سبب التحويل مطلوب.',
            'reason.min' => 'سبب التحويل يجب ألا يقل عن 5 أحرف.',
            'reason.max' => 'سبب التحويل يجب ألا يتجاوز 1000 حرف.',
        ];
    }
}
