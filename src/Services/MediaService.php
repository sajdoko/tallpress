<?php

namespace Sajdoko\TallPress\Services;

use Illuminate\Http\UploadedFile;
use Sajdoko\TallPress\Models\Media;

class MediaService
{
    /**
     * Upload and store a media file.
     */
    public function upload(UploadedFile $file, ?int $uploadedBy = null): Media
    {
        $this->validateFile($file);

        return Media::createFromUpload($file, $uploadedBy);
    }

    /**
     * Validate the uploaded file.
     */
    protected function validateFile(UploadedFile $file): void
    {
        $maxSize = tallpress_setting('images_max_size', 2048) * 1024; // Convert KB to bytes
        $allowedMimes = config('tallpress.images.allowed_mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedMimes)) {
            throw new \InvalidArgumentException('File type not allowed.');
        }
    }

    /**
     * Delete media and its file.
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }
}
