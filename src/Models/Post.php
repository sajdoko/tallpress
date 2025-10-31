<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\CommonMarkConverter;
use Sajdoko\TallPress\Database\Factories\PostFactory;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $seo_title
 * @property string|null $meta_description
 * @property array|null $schema_markup
 * @property string|null $excerpt
 * @property string $body
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $author_id
 * @property string|null $featured_image
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $body_html
 * @property-read string|null $featured_image_url
 */
class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tallpress_posts';

    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'meta_description',
        'schema_markup',
        'excerpt',
        'body',
        'status',
        'published_at',
        'author_id',
        'featured_image',
        'meta',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'schema_markup' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PostFactory::new();
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        $authorModel = config('tallpress.author_model', 'App\\Models\\User');

        return $this->belongsTo($authorModel, 'author_id');
    }

    /**
     * Get the categories for the post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'tallpress_post_category', 'post_id', 'category_id');
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tallpress_post_tag', 'post_id', 'tag_id');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Get the approved comments for the post.
     */
    public function approvedComments(): HasMany
    {
        return $this->comments()->where('approved', true);
    }

    /**
     * Get the revisions for the post.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class, 'post_id')->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include pending posts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter posts by author.
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope a query to search posts.
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
        });
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (empty($this->featured_image)) {
            return null;
        }

        // If it's already a full URL (external image), return it as-is
        if ($this->isExternalUrl($this->featured_image)) {
            return $this->featured_image;
        }

        // Otherwise, it's a local path - generate URL from storage
        return Storage::disk(config('tallpress.storage_disk', 'public'))->url($this->featured_image);
    }

    /**
     * Check if a string is an external URL.
     */
    protected function isExternalUrl(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false
            && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
    }

    /**
     * Render the body as HTML from Markdown or sanitize rich HTML.
     */
    public function getBodyHtmlAttribute(): string
    {
        // Return empty string if body is null
        if ($this->body === null) {
            return '';
        }

        // If editor type is rich and body contains HTML tags, sanitize HTML
        if (tallpress_setting('editor_type', 'markdown') === 'rich' && $this->isRichHtml()) {
            $sanitizer = new \Sajdoko\TallPress\Services\HtmlSanitizer;

            return $sanitizer->sanitize($this->body);
        }

        // Otherwise, convert from Markdown
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convert($this->body)->getContent();
    }

    /**
     * Check if body contains HTML tags (rich editor content).
     */
    protected function isRichHtml(): bool
    {
        // Return false if body is null
        if ($this->body === null) {
            return false;
        }

        // Simple check: if body contains HTML tags like <p>, <div>, etc.
        return preg_match('/<(p|div|h[1-6]|ul|ol|li|blockquote)[\s>]/i', $this->body) === 1;
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at <= now();
    }

    /**
     * Check if the post is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the post is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the views count from meta field.
     */
    public function getViewsCountAttribute(): int
    {
        return (int) ($this->meta['views'] ?? 0);
    }

    /**
     * Increment the views count for this post.
     */
    public function incrementViews(): void
    {
        $meta = $this->meta ?? [];
        $meta['views'] = ($meta['views'] ?? 0) + 1;
        $this->meta = $meta;
        $this->save();
    }
}
