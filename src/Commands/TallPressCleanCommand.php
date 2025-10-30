<?php

namespace Sajdoko\TallPress\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Sajdoko\TallPress\Models\Post;

class TallPressCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tallpress:clean {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned blog images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning for orphaned blog images...');

        $disk = Storage::disk(config('tallpress.storage_disk', 'public'));
        $imagePath = config('tallpress.images.path', 'tallpress/images');

        if (! $disk->exists($imagePath)) {
            $this->info('No blog images directory found.');

            return self::SUCCESS;
        }

        // Get all image files
        $allFiles = $disk->allFiles($imagePath);

        // Get all images referenced in posts (including soft-deleted)
        $referencedImages = Post::withTrashed()
            ->whereNotNull('featured_image')
            ->pluck('featured_image')
            ->toArray();

        $orphanedFiles = [];

        foreach ($allFiles as $file) {
            if (! in_array($file, $referencedImages)) {
                $orphanedFiles[] = $file;
            }
        }

        if (empty($orphanedFiles)) {
            $this->info('No orphaned images found.');

            return self::SUCCESS;
        }

        $this->info('Found '.count($orphanedFiles).' orphaned image(s):');

        foreach ($orphanedFiles as $file) {
            $this->line('  - '.$file);
        }

        if ($this->option('dry-run')) {
            $this->info('');
            $this->info('Dry run - no files were deleted.');
            $this->info('Run without --dry-run to delete these files.');

            return self::SUCCESS;
        }

        if ($this->confirm('Do you want to delete these files?', false)) {
            $deleted = 0;

            foreach ($orphanedFiles as $file) {
                if ($disk->delete($file)) {
                    $deleted++;
                }
            }

            $this->info("Deleted {$deleted} orphaned image(s).");
        } else {
            $this->info('Operation cancelled.');
        }

        return self::SUCCESS;
    }
}
