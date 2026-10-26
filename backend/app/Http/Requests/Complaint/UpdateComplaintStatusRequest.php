<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateComplaintStatusRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:new,under_review,assigned,in_progress,resolved,rejected,closed,reopened'],
            'notes' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
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
            'notes.required_if' => 'سبب رفض البلاغ مطلوب وإلزامي لتوضيح القرار للمواطن.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
            'reason.max' => 'سبب التحديث يجب ألا يتجاوز 500 حرف.',
        ];
    }
}
