<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreProjectRequest extends BaseApiRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('title') && $this->has('name')) {
            $merge['title'] = $this->input('name');
        }

        if (! $this->has('target_amount') && $this->has('budget')) {
            $merge['target_amount'] = $this->input('budget');
        }

        if (! $this->has('department_id') && $this->user()?->department_id) {
            $merge['department_id'] = $this->user()->department_id;
        }

        if ($merge) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['nullable', 'string', 'in:draft,published,active,completed,suspended,planning,in_progress,halted'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
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
            'title.required' => 'عنوان المشروع مطلوب.',
            'title.max' => 'عنوان المشروع يجب ألا يتجاوز 255 حرفاً.',
            'description.required' => 'وصف المشروع مطلوب.',
            'department_id.required' => 'الجهة / الإدارة المختصة بالمشروع مطلوبة.',
            'department_id.exists' => 'الجهة المحددة غير موجودة.',
            'target_amount.required' => 'الميزانية / المبلغ المستهدف للمشروع مطلوب.',
            'target_amount.numeric' => 'المبلغ المستهدف يجب أن يكون قيمة عددية.',
            'target_amount.min' => 'المبلغ المستهدف يجب أن يكون صفراً أو أكثر.',
            'latitude.required' => 'إحداثيات خط العرض مطلوبة.',
            'latitude.between' => 'إحداثيات خط العرض يجب أن تكون بين -90 و 90.',
            'longitude.required' => 'إحداثيات خط الطول مطلوبة.',
            'longitude.between' => 'إحداثيات خط الطول يجب أن تكون بين -180 و 180.',
            'status.in' => 'حالة المشروع المحددة غير صالحة.',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون jpeg أو png أو jpg أو webp.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }
}
