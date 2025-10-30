<?php

namespace Sajdoko\TallPress\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Sajdoko\TallPress\Models\Media;
use Sajdoko\TallPress\Models\Post;

class TallPressReorganizeImagesCommand extends Command
{
    protected $signature = 'tallpress:reorganize-images 
                            {--dry-run : Run without making actual changes}';

    protected $description = 'Reorganize blog images into year/month structure';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in dry-run mode. No changes will be made.');
        }

        $disk = config('tallpress.storage_disk', 'public');
        $basePath = config('tallpress.images.path', 'tallpress/images');

        // Get all posts with featured images
        $posts = Post::whereNotNull('featured_image')->get();

        $this->info("Found {$posts->count()} posts with featured images.");

        $movedCount = 0;
        $errorCount = 0;

        foreach ($posts as $post) {
            $oldPath = $post->featured_image;

            // Skip if already organized by year/month
            if (preg_match('#/\d{4}/\d{2}/#', $oldPath)) {
                continue;
            }

            if (! Storage::disk($disk)->exists($oldPath)) {
                $this->warn("File not found: {$oldPath}");
                $errorCount++;

                continue;
            }

            // Generate new path based on post creation date
            $date = $post->created_at ?? now();
            $yearMonth = $date->format('Y/m');
            $filename = basename($oldPath);
            $newPath = "{$basePath}/{$yearMonth}/{$filename}";

            // Ensure unique filename
            $counter = 1;
            $originalNewPath = $newPath;
            while (Storage::disk($disk)->exists($newPath)) {
                $info = pathinfo($originalNewPath);
                $newPath = $info['dirname'].'/'.$info['filename'].'-'.$counter.'.'.$info['extension'];
                $counter++;
            }

            if ($isDryRun) {
                $this->line("Would move: {$oldPath} → {$newPath}");
            } else {
                try {
                    // Create directory if it doesn't exist
                    $directory = dirname($newPath);
                    if (! Storage::disk($disk)->exists($directory)) {
                        Storage::disk($disk)->makeDirectory($directory);
                    }

                    // Move the file
                    Storage::disk($disk)->move($oldPath, $newPath);

                    // Update post
                    $post->update(['featured_image' => $newPath]);

                    // Create media record if it doesn't exist
                    if (! Media::where('path', $newPath)->exists()) {
                        $fullPath = Storage::disk($disk)->path($newPath);
                        $width = null;
                        $height = null;

                        if (file_exists($fullPath)) {
                            $imageSize = @getimagesize($fullPath);
                            if ($imageSize !== false) {
                                $width = $imageSize[0];
                                $height = $imageSize[1];
                            }
                        }

                        Media::create([
                            'filename' => $filename,
                            'path' => $newPath,
                            'disk' => $disk,
                            'mime_type' => Storage::disk($disk)->mimeType($newPath),
                            'size' => Storage::disk($disk)->size($newPath),
                            'width' => $width,
                            'height' => $height,
                            'uploaded_by' => $post->author_id,
                        ]);
                    }

                    $this->info("Moved: {$oldPath} → {$newPath}");
                    $movedCount++;
                } catch (\Exception $e) {
                    $this->error("Error moving {$oldPath}: ".$e->getMessage());
                    $errorCount++;
                }
            }
        }

        if ($isDryRun) {
            $this->info("\nDry run complete. {$movedCount} files would be moved.");
        } else {
            $this->info("\nReorganization complete!");
            $this->info("Moved: {$movedCount}");
            if ($errorCount > 0) {
                $this->warn("Errors: {$errorCount}");
            }
        }

        return self::SUCCESS;
    }
}
