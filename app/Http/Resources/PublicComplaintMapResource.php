<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicComplaintMapResource extends JsonResource
{
    /**
     * Transform the resource into a lightweight map pin payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'complaint_number' => $this->complaint_number,
            'title' => $this->title,
            'status' => $this->status,
            'status_arabic' => $this->status_arabic,
            'priority' => $this->priority,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'likes_count' => (int) $this->likes_count,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
