<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivateImageService
{
    public function store(UploadedFile $file, string $directory): array
    {
        if (! function_exists('imagecreatefromstring')) {
            throw ValidationException::withMessages(['images' => 'Image processing is unavailable. Enable the PHP GD extension.']);
        }
        $size = @getimagesize($file->getRealPath());
        if (! $size || $size[0] * $size[1] > 16000000) {
            throw ValidationException::withMessages(['images' => 'The image must contain at most 16 million pixels.']);
        }
        $image = @imagecreatefromstring($file->getContent());
        if (! $image) {
            throw ValidationException::withMessages(['images' => 'The image could not be decoded.']);
        }
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);
        $path = $directory.'/'.Str::uuid().'.png';
        Storage::disk('clinical')->put($path, $bytes);

        return ['file_path' => $path, 'original_name' => null, 'mime_type' => 'image/png', 'file_size' => strlen($bytes)];
    }
}
