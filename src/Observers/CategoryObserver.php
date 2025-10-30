<?php

namespace Sajdoko\TallPress\Observers;

use Illuminate\Support\Str;
use Sajdoko\TallPress\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     */
    public function creating(Category $category): void
    {
        // Auto-generate slug if not provided
        if (empty($category->slug)) {
            $category->slug = $this->generateUniqueSlug($category->name);
        }
    }

    /**
     * Handle the Category "updating" event.
     */
    public function updating(Category $category): void
    {
        // Regenerate slug if name changed and slug is empty or matches old name slug
        if ($category->isDirty('name') && empty($category->slug)) {
            $category->slug = $this->generateUniqueSlug($category->name, $category->id);
        }
    }

    /**
     * Generate a unique slug for the category.
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
        $query = Category::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
