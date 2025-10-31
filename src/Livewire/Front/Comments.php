<?php

namespace Sajdoko\TallPress\Livewire\Front;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;

class Comments extends Component
{
    public Post $post;

    #[Validate('required|string|max:255')]
    public string $author_name = '';

    #[Validate('required|email|max:255')]
    public string $author_email = '';

    #[Validate('required|string|max:1000')]
    public string $body = '';

    public bool $commentsEnabled = true;

    public bool $requireApproval = true;

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->commentsEnabled = tallpress_setting('comments_enabled', true);
        $this->requireApproval = tallpress_setting('comments_require_approval', true);

        // Load comments relationship
        $this->post->load('approvedComments');
    }

    public function submit(): void
    {
        // Check if comments are enabled
        if (! $this->commentsEnabled) {
            session()->flash('error', 'Comments are disabled.');

            return;
        }

        // Only published posts can receive comments
        if (! $this->post->isPublished()) {
            session()->flash('error', 'Cannot comment on unpublished posts.');

            return;
        }

        // Validate the form
        $this->validate();

        // Create the comment
        Comment::create([
            'post_id' => $this->post->id,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'body' => $this->body,
            'approved' => ! $this->requireApproval,
        ]);

        // Reset form fields
        $this->reset(['author_name', 'author_email', 'body']);

        // Reload comments to show the new one (if auto-approved)
        $this->post->load('approvedComments');

        // Flash success message
        $message = $this->requireApproval
            ? 'Your comment has been submitted and is awaiting approval.'
            : 'Your comment has been posted successfully.';

        session()->flash('success', $message);
    }

    public function render()
    {
        return view('tallpress::front.livewire.comments');
    }
}
