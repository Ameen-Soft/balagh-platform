<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload an individual file to the specified directory.
     *
     * @return array{file_path: string, file_name: string, file_type: string, file_size: int}
     */
    public function upload(UploadedFile $file, string $folder = 'general', ?string $disk = null): array
    {
        $disk = $disk ?? config('balagh.storage.disk', 'public');
        $fileName = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($folder, $fileName, $disk);

        return [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Upload multiple files.
     *
     * @param  array<UploadedFile>  $files
     * @return array<array{file_path: string, file_name: string, file_type: string, file_size: int}>
     */
    public function uploadMultiple(array $files, string $folder = 'general', ?string $disk = null): array
    {
        $uploaded = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded[] = $this->upload($file, $folder, $disk);
            }
        }

        return $uploaded;
    }

    /**
     * Delete a file from storage.
     */
    public function delete(string $filePath, ?string $disk = null): bool
    {
        $disk = $disk ?? config('balagh.storage.disk', 'public');

        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }
}
