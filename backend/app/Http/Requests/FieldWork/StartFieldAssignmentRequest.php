<?php

namespace App\Http\Requests\FieldWork;

use App\Http\Requests\BaseApiRequest;

class StartFieldAssignmentRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90 درجة.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180 درجة.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 500 حرف.',
        ];
    }
}
