<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array with citizen privacy redaction.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user() ?? auth('sanctum')->user();

        return [
            'id' => $this->id,
            'complaint_number' => $this->complaint_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_arabic' => $this->status_arabic,
            'priority' => $this->priority,
            'priority_arabic' => $this->priority_arabic,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'likes_count' => (int) $this->likes_count,
            'is_liked_by_me' => $user ? $this->isLikedBy($user) : false,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'department' => $this->whenLoaded('currentDepartment', function () {
                return [
                    'id' => $this->currentDepartment->id,
                    'name' => $this->currentDepartment->name,
                    'ministry' => $this->currentDepartment->relationLoaded('ministry') && $this->currentDepartment->ministry ? [
                        'id' => $this->currentDepartment->ministry->id,
                        'name' => $this->currentDepartment->ministry->name,
                    ] : null,
                ];
            }),
            'attachments' => ComplaintAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
