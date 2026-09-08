<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseApiRequest;

class FilterComplaintRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:new,under_review,in_progress,resolved,rejected,closed'],
            'ministry_id' => ['nullable', 'integer', 'exists:ministries,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'sort_by' => ['nullable', 'string', 'in:created_at,priority,status,tracking_number'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
