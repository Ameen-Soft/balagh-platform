<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ministry_id' => $this->ministry_id,
            'parent_id' => $this->parent_id,
            'department_id' => $this->department_id,
            'name' => $this->name,
            'description' => $this->description,
            'level' => (int) $this->level,
            'is_active' => (bool) $this->is_active,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'complaints_count' => $this->whenCounted('complaints'),
            'total_complaints_count' => $this->total_complaints_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
