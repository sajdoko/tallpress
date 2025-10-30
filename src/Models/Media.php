<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sajdoko\TallPress\Database\Factories\MediaFactory;

/**
 * @property int $id
 * @property string $filename
 * @property string $path
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property int $uploaded_by
 * @property string|null $alt_text
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Media extends Model
{
    use HasFactory;

    protected $table = 'tallpress_media';

    protected $fillable = [
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'uploaded_by',
        'alt_text',
        'description',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return MediaFactory::new();
    }

    /**
     * Get the user who uploaded the media.
     */
    public function uploader(): BelongsTo
    {
        $authorModel = config('tallpress.author_model', 'App\\Models\\User');

        return $this->belongsTo($authorModel, 'uploaded_by');
    }

    /**
     * Create media from uploaded file with year/month organization.
     */
    public static function createFromUpload(UploadedFile $file, ?int $uploadedBy = null): self
    {
        $disk = config('tallpress.storage_disk', 'public');
        $basePath = config('tallpress.images.path', 'images');

        // Generate year/month path structure
        $yearMonth = now()->format('Y/m');
        $path = "{$basePath}/{$yearMonth}";

        // Generate filename - use SEO-friendly name if configured
        if (tallpress_setting('images_use_seo_filenames', true)) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            // Create SEO-friendly slug from filename
            $slug = \Illuminate\Support\Str::slug($originalName);

            // Ensure uniqueness by checking if file exists
            $filename = $slug.'.'.$extension;
            $counter = 1;
            while (Storage::disk($disk)->exists("{$path}/{$filename}")) {
                $filename = $slug.'-'.$counter.'.'.$extension;
                $counter++;
            }
        } else {
            $filename = $file->hashName();
        }

        $storedPath = $file->storeAs($path, $filename, $disk);

        // Get image dimensions if it's an image
        $width = null;
        $height = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $fullPath = Storage::disk($disk)->path($storedPath);
                $imageSize = getimagesize($fullPath);
                if ($imageSize !== false) {
                    $width = $imageSize[0];
                    $height = $imageSize[1];
                }
            } catch (\Exception $e) {
                // Silently fail if we can't get dimensions
            }
        }

        return self::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    /**
     * Get the full URL of the media.
     */
    public function getUrlAttribute(): string
    {
        $url = Storage::disk($this->disk)->url($this->path);

        // If the configured APP_URL doesn't match the current request URL,
        // replace it with the current request URL base (helpful in development)
        if (request() && request()->getSchemeAndHttpHost()) {
            $configuredUrl = config('app.url');
            $currentUrl = request()->getSchemeAndHttpHost();

            if ($configuredUrl && $configuredUrl !== $currentUrl && str_contains($url, $configuredUrl)) {
                $url = str_replace($configuredUrl, $currentUrl, $url);
            }
        }

        return $url;
    }

    /**
     * Delete the media file from storage.
     */
    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    /**
     * Delete the model and the file.
     */
    public function delete()
    {
        $this->deleteFile();

        return parent::delete();
    }
}
