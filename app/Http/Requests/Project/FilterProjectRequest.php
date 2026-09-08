<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;

class FilterProjectRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:proposed,approved,in_progress,completed,cancelled'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'ministry_id' => ['nullable', 'integer', 'exists:ministries,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', 'in:created_at,target_amount,current_amount,start_date'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
