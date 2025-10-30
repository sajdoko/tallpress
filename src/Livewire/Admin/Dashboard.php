<?php

namespace Sajdoko\TallPress\Livewire\Admin;

use Livewire\Component;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

class Dashboard extends Component
{
    public array $stats = [];

    public $recentPosts;

    public $recentComments;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $userModel = config('tallpress.author_model', 'App\\Models\\User');
        $roleField = config('tallpress.roles.role_field', 'role');

        $this->stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::published()->count(),
            'draft_posts' => Post::draft()->count(),
            'pending_posts' => Post::pending()->count(),
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
            'total_comments' => Comment::count(),
            'pending_comments' => Comment::where('approved', false)->count(),
            'total_users' => $userModel::count(),
            'admin_users' => $userModel::where($roleField, 'admin')->count(),
            'editor_users' => $userModel::where($roleField, 'editor')->count(),
            'author_users' => $userModel::where($roleField, 'author')->count(),
        ];

        $this->recentPosts = Post::with('author', 'categories')
            ->latest()
            ->limit(5)
            ->get();

        $this->recentComments = Comment::with('post')
            ->whereHas('post') // Only comments with non-deleted posts
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('tallpress::admin.livewire.dashboard')
            ->layout('tallpress::admin.layout');
    }
}
