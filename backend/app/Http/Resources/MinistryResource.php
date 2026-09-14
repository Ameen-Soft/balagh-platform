<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinistryResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'logo' => $this->logo,
            'contact_email' => $this->contact_email,
            'is_active' => (bool) $this->is_active,
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
            'departments_count' => $this->whenCounted('departments'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
