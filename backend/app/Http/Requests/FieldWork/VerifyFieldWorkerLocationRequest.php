<?php

namespace App\Http\Requests\FieldWork;

use App\Http\Requests\BaseApiRequest;

class VerifyFieldWorkerLocationRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
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
            'latitude.required' => 'إحداثي خط العرض مطلوب للتحقق من الموقع.',
            'latitude.numeric' => 'خط العرض يجب أن يكون قيمة عددية صالحة.',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90 درجة.',
            'longitude.required' => 'إحداثي خط الطول مطلوب للتحقق من الموقع.',
            'longitude.numeric' => 'خط الطول يجب أن يكون قيمة عددية صالحة.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180 درجة.',
        ];
    }
}
