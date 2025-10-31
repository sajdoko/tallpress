<?php

namespace Sajdoko\TallPress\Http\Controllers;

use Illuminate\View\View;
use Sajdoko\TallPress\Models\Post;

class PostController
{
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

        return view('tallpress::front.show', compact('post'));
    }
}
