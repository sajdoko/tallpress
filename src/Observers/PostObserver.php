<?php

namespace Sajdoko\TallPress\Observers;

use Illuminate\Support\Str;
use Sajdoko\TallPress\Events\PostPublished;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\PostRevision;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }

        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
    }

    /**
     * Handle the Post "updating" event.
     */
    public function updating(Post $post): void
    {
        // Save revision if revisions are enabled and content changed
        if (tallpress_setting('revisions_enabled', true) && $post->exists) {
            $this->saveRevision($post);
        }

        // If status changed to published and published_at is not set
        if ($post->isDirty('status') && $post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }

        // If slug is empty or title changed, regenerate slug
        if (empty($post->slug) || ($post->isDirty('title') && empty($post->getOriginal('slug')))) {
            $post->slug = $this->generateUniqueSlug($post->title, $post->id);
        }
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // If status changed to published, fire PostPublished event
        if ($post->wasChanged('status') && $post->status === 'published') {
            event(new PostPublished($post));
        }
    }

    /**
     * Generate a unique slug from the given title.
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists.
     */
    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Post::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Save a revision of the post before updating.
     */
    protected function saveRevision(Post $post): void
    {
        // Check if any content fields have changed
        $contentChanged = $post->isDirty(['title', 'excerpt', 'body', 'meta']);

        if (! $contentChanged) {
            return;
        }

        // Create revision from original values
        PostRevision::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'title' => $post->getOriginal('title'),
            'excerpt' => $post->getOriginal('excerpt'),
            'body' => $post->getOriginal('body'),
            'meta' => $post->getOriginal('meta'),
            'created_at' => now(),
        ]);

        // Keep only the configured number of revisions
        $keepRevisions = tallpress_setting('revisions_keep', 10);
        $revisions = PostRevision::where('post_id', $post->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($revisions->count() > $keepRevisions) {
            $revisions->skip($keepRevisions)->each(fn ($revision) => $revision->delete());
        }
    }
}
