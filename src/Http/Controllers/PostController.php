<?php

namespace Sajdoko\TallPress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Sajdoko\TallPress\Models\Post;

class PostController
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): View
    {
        $query = Post::with(['author', 'categories', 'tags'])
            ->published()
            ->latest('published_at');

        // Filter by category
        if ($categorySlug = $request->get('category')) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by tag
        if ($tagSlug = $request->get('tag')) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Only apply search if search is enabled and a search term is provided
        if (tallpress_setting('search_enabled', true) && ($search = $request->get('search'))) {
            $query->search($search);
        }

        $posts = $query->paginate(tallpress_setting('per_page', 15));

        return view('tallpress::front.home', compact('posts'));
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): View
    {
        // Only show published posts to non-authors
        if (! $post->isPublished() && optional($post->author)->isNot(auth()->user())) {
            abort(404);
        }

        // Load relationships - only load comments if comments are enabled
        $relationships = ['author', 'categories', 'tags'];
        if (tallpress_setting('comments_enabled', true)) {
            $relationships[] = 'approvedComments';
        }

        $post->load($relationships);

        // Increment views count (only once per session)
        $this->trackPostView($post);

        return view('tallpress::front.show', compact('post'));
    }

    /**
     * Track a post view (once per session).
     */
    protected function trackPostView(Post $post): void
    {
        $sessionKey = "blog_post_viewed_{$post->id}";

        // Only increment if not already viewed in this session
        if (! session()->has($sessionKey)) {
            $post->incrementViews();
            session()->put($sessionKey, true);
        }
    }
}
