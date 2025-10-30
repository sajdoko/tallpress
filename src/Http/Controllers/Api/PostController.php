<?php

namespace Sajdoko\TallPress\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Sajdoko\TallPress\Http\Requests\StorePostRequest;
use Sajdoko\TallPress\Http\Requests\UpdatePostRequest;
use Sajdoko\TallPress\Http\Resources\PostResource;
use Sajdoko\TallPress\Models\Post;

class PostController
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::with(['author', 'categories', 'tags'])
            ->published()
            ->latest('published_at');

        // Only apply search if search is enabled and a search term is provided
        if (tallpress_setting('search_enabled', true) && ($search = $request->get('search'))) {
            $query->search($search);
        }

        $posts = $query->paginate(tallpress_setting('per_page', 15));

        return PostResource::collection($posts);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): PostResource
    {
        // Only show published posts to non-authors
        if (! $post->isPublished() && optional($post->author)->isNot(auth()->user())) {
            abort(404);
        }

        // Load relationships - only load comments if comments are enabled
        $relationships = ['author', 'categories', 'tags'];
        if (tallpress_setting('comments_enabled', true)) {
            $relationships[] = 'comments';
        }

        $post->load($relationships);

        return new PostResource($post);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $data = $request->validated();

        // Handle image upload or external URL
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadImage($request->file('featured_image'));
        } elseif (isset($data['featured_image']) && is_string($data['featured_image'])) {
            // External URL - keep it as-is (validation already ensures it's valid)
            // No upload needed
        }

        // Set author to current user
        $data['author_id'] = auth()->id();

        $post = Post::create($data);

        // Sync relationships
        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        $post->load(['author', 'categories', 'tags']);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);

        $data = $request->validated();

        // Handle image upload or external URL
        if ($request->hasFile('featured_image')) {
            // Delete old image (only if it's a local file, not external URL)
            if ($post->featured_image && ! $this->isExternalUrl($post->featured_image)) {
                $this->deleteImage($post->featured_image);
            }

            $data['featured_image'] = $this->uploadImage($request->file('featured_image'));
        } elseif (isset($data['featured_image']) && is_string($data['featured_image'])) {
            // External URL - keep it as-is (validation already ensures it's valid)
            // Delete old image if it was a local file
            if ($post->featured_image && ! $this->isExternalUrl($post->featured_image)) {
                $this->deleteImage($post->featured_image);
            }
        }

        $post->update($data);

        // Sync relationships
        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        } else {
            $post->categories()->detach();
        }

        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        } else {
            $post->tags()->detach();
        }

        $post->load(['author', 'categories', 'tags']);

        return new PostResource($post);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        // Delete featured image (only if it's a local file, not external URL)
        if ($post->featured_image && ! $this->isExternalUrl($post->featured_image)) {
            $this->deleteImage($post->featured_image);
        }

        $post->delete();

        return response()->json(null, 204);
    }

    /**
     * Upload an image file.
     */
    protected function uploadImage($file): string
    {
        $path = config('tallpress.images.path', 'tallpress/images');
        $disk = config('tallpress.storage_disk', 'public');

        return $file->store($path, $disk);
    }

    /**
     * Delete an image file.
     */
    protected function deleteImage(string $path): void
    {
        $disk = config('tallpress.storage_disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
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
     * Authorize a given action.
     */
    protected function authorize(string $ability, $arguments = []): void
    {
        if (! auth()->user()?->can($ability, $arguments)) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
