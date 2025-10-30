<?php

namespace Sajdoko\TallPress\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Sajdoko\TallPress\Models\Post;

class PostPublished
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Post $post) {}
}
