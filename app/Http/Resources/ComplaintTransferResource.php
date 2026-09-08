<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintTransferResource extends JsonResource
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
            'complaint_id' => $this->complaint_id,
            'from_department' => $this->whenLoaded('fromDepartment', function () {
                return [
                    'id' => $this->fromDepartment->id,
                    'name' => $this->fromDepartment->name,
                ];
            }),
            'to_department' => $this->whenLoaded('toDepartment', function () {
                return [
                    'id' => $this->toDepartment->id,
                    'name' => $this->toDepartment->name,
                ];
            }),
            'reason' => $this->reason,
            'transferred_by' => new UserResource($this->whenLoaded('transferredBy')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
