<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectContributionResource extends JsonResource
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
            'project_id' => $this->project_id,
            'amount' => (float) $this->amount,
            'contributor' => new UserResource($this->whenLoaded('contributor')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
