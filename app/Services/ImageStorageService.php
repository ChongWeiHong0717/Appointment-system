<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStorageService
{
    public function replace(?UploadedFile $image, ?string $currentPath, string $directory): ?string
    {
        if (! $image) {
            return $currentPath;
        }

        $newPath = $image->store($directory, 'public');

        if ($currentPath) {
            Storage::disk('public')->delete($currentPath);
        }

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
