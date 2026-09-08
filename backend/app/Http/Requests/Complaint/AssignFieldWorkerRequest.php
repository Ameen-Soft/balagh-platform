<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;

class AssignFieldWorkerRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:today'],
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
            'user_id.required' => 'الموظف الميداني المطلوب إسناد المهمة إليه إلزامي.',
            'user_id.exists' => 'المستخدم المحدد غير موجود.',
            'scheduled_at.after_or_equal' => 'تاريخ الجدولة يجب أن يكون اليوم أو تاريخاً مستقبلياً.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}
