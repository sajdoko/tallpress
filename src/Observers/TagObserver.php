<?php

namespace Sajdoko\TallPress\Observers;

use Illuminate\Support\Str;
use Sajdoko\TallPress\Models\Tag;

class TagObserver
{
    /**
     * Handle the Tag "creating" event.
     */
    public function creating(Tag $tag): void
    {
        // Auto-generate slug if not provided
        if (empty($tag->slug)) {
            $tag->slug = $this->generateUniqueSlug($tag->name);
        }
    }

    /**
     * Handle the Tag "updating" event.
     */
    public function updating(Tag $tag): void
    {
        // Regenerate slug if name changed and slug is empty or matches old name slug
        if ($tag->isDirty('name') && empty($tag->slug)) {
            $tag->slug = $this->generateUniqueSlug($tag->name, $tag->id);
        }
    }

    /**
     * Generate a unique slug for the tag.
     */
    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists.
     */
    protected function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Tag::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
