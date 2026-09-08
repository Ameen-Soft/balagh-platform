<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;

class StoreComplaintRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,png,jpg,pdf,mp4', 'max:10240'],
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
            'title.required' => 'عنوان البلاغ مطلوب.',
            'title.max' => 'عنوان البلاغ يجب ألا يتجاوز 255 حرفاً.',
            'description.required' => 'وصف البلاغ مطلوب.',
            'description.min' => 'وصف البلاغ يجب أن لا يقل عن 10 أحرف لتوضيح المشكلة.',
            'category_id.required' => 'تصنيف البلاغ مطلوب.',
            'category_id.exists' => 'التصنيف المحدد غير موجود.',
            'latitude.required' => 'إحداثيات خط العرض مطلوبة.',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90 درجة.',
            'longitude.required' => 'إحداثيات خط الطول مطلوبة.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180 درجة.',
            'priority.in' => 'مستوى الأولوية المحدد غير صالح.',
            'attachments.max' => 'الحد الأقصى للمرفقات هو 5 ملفات.',
            'attachments.*.mimes' => 'صيغ المرفقات المسموح بها هي: jpeg, png, jpg, pdf, mp4.',
            'attachments.*.max' => 'حجم المرفق الواحد يجب ألا يتجاوز 10 ميجابايت.',
        ];
    }
}
