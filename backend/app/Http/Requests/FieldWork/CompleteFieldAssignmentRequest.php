<?php

namespace App\Http\Requests\FieldWork;

use App\Http\Requests\BaseApiRequest;

class CompleteFieldAssignmentRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report' => ['required', 'string', 'min:10', 'max:3000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
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
            'report.required' => 'التقرير الميداني مطلوب لإتمام المهمة.',
            'report.min' => 'التقرير الميداني يجب أن لا يقل عن 10 أحرف لتوضيح الإجراء المتخذ.',
            'latitude.required' => 'إحداثيات الموقع الفعلي للباحث الميداني مطلوبة للتحقق الجغرافي.',
            'longitude.required' => 'إحداثيات الموقع الفعلي للباحث الميداني مطلوبة للتحقق الجغرافي.',
            'attachments.max' => 'الحد الأقصى لمرفقات إثبات الإنجاز هو 5 ملفات.',
            'attachments.*.mimes' => 'صيغ المرفقات المسموح بها: jpeg, png, jpg, pdf.',
            'attachments.*.max' => 'حجم المرفق الواحد يجب ألا يتجاوز 10 ميجابايت.',
        ];
    }
}
