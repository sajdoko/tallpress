<?php

namespace Sajdoko\TallPress\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;

class CommentController
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        // Check if comments are enabled
        if (! tallpress_setting('comments_enabled', true)) {
            return redirect()->back()->with('error', 'Comments are disabled.');
        }

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_email' => ['required', 'email', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        // Only published posts can receive comments
        if (! $post->isPublished()) {
            return redirect()->back()->with('error', 'Cannot comment on unpublished posts.');
        }

        Comment::create([
            'post_id' => $post->id,
            'author_name' => $validated['author_name'],
            'author_email' => $validated['author_email'],
            'body' => $validated['body'],
            'approved' => ! tallpress_setting('comments_require_approval', true),
        ]);

        $message = tallpress_setting('comments_require_approval', true)
            ? 'Your comment has been submitted and is awaiting approval.'
            : 'Your comment has been posted successfully.';

        return redirect()->back()->with('success', $message);
    }
}
