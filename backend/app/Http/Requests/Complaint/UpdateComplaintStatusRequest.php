<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;

class UpdateComplaintStatusRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:new,under_review,in_progress,resolved,rejected,closed'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:500'],
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
            'status.required' => 'حالة البلاغ الجديدة مطلوبة.',
            'status.in' => 'حالة البلاغ المحددة غير صالحة.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
            'reason.max' => 'سبب التحديث يجب ألا يتجاوز 500 حرف.',
        ];
    }
}
