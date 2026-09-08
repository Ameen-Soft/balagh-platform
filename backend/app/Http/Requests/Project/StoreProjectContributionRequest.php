<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;

class StoreProjectContributionRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'payment_method' => ['required', 'string', 'in:mada,visa,mastercard,bank_transfer,simulated'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
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
            'amount.required' => 'مبلغ المساهمة مطلوب.',
            'amount.numeric' => 'مبلغ المساهمة يجب أن يكون قيمة رقمية.',
            'amount.min' => 'الحد الأدنى للمساهمة هو 1 ريال.',
            'payment_method.required' => 'طريقة الدفع مطلوبة.',
            'payment_method.in' => 'طريقة الدفع المحددة غير مدعومة.',
        ];
    }
}
