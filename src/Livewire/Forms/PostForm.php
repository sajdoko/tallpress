<?php

namespace Sajdoko\TallPress\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\WithFileUploads;
use Sajdoko\TallPress\Models\Post;

class PostForm extends Form
{
    use WithFileUploads;

    public ?Post $post = null;

    #[Validate]
    public $title = '';

    #[Validate]
    public $slug = '';

    public $seo_title = '';

    public $meta_description = '';

    public $excerpt = '';

    #[Validate('required')]
    public $body = '';

    #[Validate('required|in:draft,pending,published')]
    public $status = 'draft';

    #[Validate('nullable|image|max:10240')]
    public $featured_image;

    public $existing_featured_image = null;

    public $featured_image_from_library = null;

    public $featured_image_external_url = null;

    public $remove_featured_image = false;

    public $meta = [];

    public $selectedCategories = [];

    public $selectedTags = [];

    protected function rules()
    {
        $postId = $this->post?->exists ? $this->post->id : 'NULL';

        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tallpress_posts', 'slug')->ignore($postId),
            ],
            'seo_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'status' => 'required|in:draft,pending,published',
            'featured_image' => 'nullable|image|max:10240',
            'featured_image_external_url' => 'nullable|url|regex:/\.(jpg|jpeg|png|gif|webp|svg)$/i',
            'meta' => 'nullable|array',
            'selectedCategories' => 'nullable|array',
            'selectedCategories.*' => 'exists:tallpress_categories,id',
            'selectedTags' => 'nullable|array',
            'selectedTags.*' => 'exists:tallpress_tags,id',
        ];
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->seo_title = $post->seo_title ?? '';
        $this->meta_description = $post->meta_description ?? '';
        $this->excerpt = $post->excerpt ?? '';
        $this->body = $post->body;
        $this->status = $post->status;
        $this->existing_featured_image = $post->featured_image;
        $this->meta = $post->meta ?? [];
        $this->selectedCategories = $post->categories->pluck('id')->toArray();
        $this->selectedTags = $post->tags->pluck('id')->toArray();
    }

    public function store()
    {
        $this->validate();

        $data = $this->getPostData();
        $data['author_id'] = auth()->id();

        $this->post = Post::create($data);

        $this->syncRelationships();
        $this->logActivity('created post');

        return $this->post;
    }

    public function update()
    {
        $this->validate();

        $data = $this->getPostData();

        $this->post->update($data);

        $this->syncRelationships();
        $this->logActivity('updated post');

        return $this->post;
    }

    protected function getPostData()
    {
        // Sanitize body if using rich editor
        $body = $this->body;
        if (tallpress_setting('editor_type', 'rich') === 'rich' && tallpress_setting('editor_sanitize_html', true)) {
            $sanitizer = new \Sajdoko\TallPress\Services\HtmlSanitizer;
            $body = $sanitizer->sanitize($body);
        }

        // Auto-generate excerpt if empty
        $excerpt = $this->excerpt;
        if (empty($excerpt) && ! empty($body)) {
            $excerpt = $this->generateExcerpt($body);
        }

        $data = [
            'title' => $this->title,
            'seo_title' => $this->seo_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'excerpt' => $excerpt,
            'body' => $body,
            'status' => $this->status,
            'meta' => $this->meta,
        ];

        if ($this->slug) {
            $data['slug'] = $this->slug;
        }

        // Auto-generate schema markup
        $data['schema_markup'] = $this->generateSchemaMarkup();

        // Handle featured image upload
        $data = $this->handleFeaturedImage($data);

        return $data;
    }

    /**
     * Generate Schema.org Article structured data.
     */
    protected function generateSchemaMarkup(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->seo_title ?: $this->title,
            'description' => $this->meta_description ?: $this->excerpt,
        ];

        if ($this->post?->exists) {
            $schema['datePublished'] = $this->post->published_at?->toIso8601String();
            $schema['dateModified'] = $this->post->updated_at->toIso8601String();

            if ($this->post->author) {
                $schema['author'] = [
                    '@type' => 'Person',
                    'name' => $this->post->author->name,
                ];
            }

            if ($this->post->featured_image_url) {
                $schema['image'] = $this->post->featured_image_url;
            }
        }

        return $schema;
    }

    /**
     * Generate an excerpt from the body content.
     */
    protected function generateExcerpt(string $body, int $maxLength = 200): string
    {
        $sanitizer = new \Sajdoko\TallPress\Services\HtmlSanitizer;

        return $sanitizer->getExcerpt($body, $maxLength);
    }

    protected function handleFeaturedImage(array $data)
    {
        if ($this->featured_image) {
            // Delete old image if updating (but only if it's a local file, not external URL)
            if ($this->post?->exists && $this->post->featured_image && ! $this->isExternalUrl($this->post->featured_image)) {
                \Illuminate\Support\Facades\Storage::disk(config('tallpress.storage_disk', 'public'))
                    ->delete($this->post->featured_image);
            }

            $path = $this->featured_image->store(
                config('tallpress.images.path', 'images'),
                config('tallpress.storage_disk', 'public')
            );
            $data['featured_image'] = $path;
        } elseif ($this->featured_image_external_url) {
            // External image URL provided
            $data['featured_image'] = $this->featured_image_external_url;

            // Reset the external URL field
            $this->featured_image_external_url = null;
        } elseif ($this->featured_image_from_library) {
            // Image selected from media library
            // Extract the path from the URL (remove domain and /storage/ prefix)
            $url = $this->featured_image_from_library;
            $path = parse_url($url, PHP_URL_PATH);

            // Remove /storage/ prefix if present
            $path = preg_replace('#^/storage/#', '', $path);

            $data['featured_image'] = $path;

            // Reset the library selection
            $this->featured_image_from_library = null;
        } elseif ($this->remove_featured_image && $this->post?->exists) {
            // Only delete from storage if it's a local file, not an external URL
            if ($this->post->featured_image && ! $this->isExternalUrl($this->post->featured_image)) {
                \Illuminate\Support\Facades\Storage::disk(config('tallpress.storage_disk', 'public'))
                    ->delete($this->post->featured_image);
            }
            $data['featured_image'] = null;
        }

        return $data;
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

    protected function syncRelationships()
    {
        if ($this->post) {
            $this->post->categories()->sync($this->selectedCategories);
            $this->post->tags()->sync($this->selectedTags);
        }
    }

    protected function logActivity($action)
    {
        if (tallpress_setting('activity_log_enabled', true) && $this->post) {
            \Sajdoko\TallPress\Models\ActivityLog::log($action, $this->post, auth()->user());
        }
    }

    public function removeFeaturedImage()
    {
        $this->remove_featured_image = true;
        $this->existing_featured_image = null;
        $this->featured_image = null;
    }
}
