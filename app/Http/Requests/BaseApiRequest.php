<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is delegated to Policies.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt for API requests.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'بيانات الإدخال غير صالحة، يرجى التحقق من الحقول المطلوبة.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
