<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'ministry' => new MinistryResource($this->whenLoaded('ministry')),
            'categories_count' => $this->whenCounted('categories'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
