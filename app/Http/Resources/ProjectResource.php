<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $target = (float) $this->target_amount;
        $current = (float) $this->current_amount;
        $progress = $target > 0 ? round(($current / $target) * 100, 2) : 0.0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'target_amount' => $target,
            'current_amount' => $current,
            'progress_percentage' => min(100.0, $progress),
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                    'ministry' => $this->department->relationLoaded('ministry') && $this->department->ministry ? [
                        'id' => $this->department->ministry->id,
                        'name' => $this->department->ministry->name,
                    ] : null,
                ];
            }),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'phases' => $this->whenLoaded('phases'),
            'contributions' => ProjectContributionResource::collection($this->whenLoaded('contributions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
