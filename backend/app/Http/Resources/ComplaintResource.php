<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'duplicate_of_id' => $this->duplicate_of_id,
            'citizen' => new UserResource($this->whenLoaded('citizen')),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'description' => $this->category->description,
                    'level' => $this->category->level,
                ];
            }),
            'department' => $this->whenLoaded('currentDepartment', function () {
                return [
                    'id' => $this->currentDepartment->id,
                    'name' => $this->currentDepartment->name,
                    'ministry_id' => $this->currentDepartment->ministry_id,
                    'ministry' => $this->currentDepartment->relationLoaded('ministry') && $this->currentDepartment->ministry ? [
                        'id' => $this->currentDepartment->ministry->id,
                        'name' => $this->currentDepartment->ministry->name,
                    ] : null,
                ];
            }),
            'attachments' => ComplaintAttachmentResource::collection($this->whenLoaded('attachments')),
            'timeline' => ComplaintTimelineResource::collection($this->whenLoaded('timeline')),
            'transfers' => ComplaintTransferResource::collection($this->whenLoaded('transfers')),
            'field_assignments' => FieldAssignmentResource::collection($this->whenLoaded('fieldAssignments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
