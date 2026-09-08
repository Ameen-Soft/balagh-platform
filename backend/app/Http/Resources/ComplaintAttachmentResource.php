<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ComplaintAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $disk = config('balagh.storage.disk', 'public');
        $url = Storage::disk($disk)->url($this->file_path);

        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'file_url' => $url,
            'file_type' => $this->file_type,
            'captured_latitude' => $this->captured_latitude,
            'captured_longitude' => $this->captured_longitude,
            'type' => $this->type,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
