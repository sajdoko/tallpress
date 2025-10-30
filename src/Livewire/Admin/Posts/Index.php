<?php

namespace Sajdoko\TallPress\Livewire\Admin\Posts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Post;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public $status = '';

    public $author = '';

    public $category = '';

    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $selectedPosts = [];

    public $selectAll = false;

    protected $queryString = [
        'status' => ['except' => ''],
        'author' => ['except' => ''],
        'category' => ['except' => ''],
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingAuthor()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPosts = $this->getPostsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedPosts = [];
        }
    }

    public function deletePost($postId)
    {
        $post = Post::findOrFail($postId);
        $this->authorize('delete', $post);

        // Log activity before deletion
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('deleted post', $post, auth()->user(), [
                'title' => $post->title,
            ]);
        }

        $post->delete();

        $this->dispatch('post-deleted');
        $this->toastSuccess('Post deleted successfully.');
    }

    public function updateStatus($postId, $newStatus)
    {
        $post = Post::findOrFail($postId);

        // Check permissions
        if ($newStatus === 'published') {
            $this->authorize('publish', $post);
        } else {
            $this->authorize('update', $post);
        }

        $post->update(['status' => $newStatus]);

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('changed post status to '.$newStatus, $post, auth()->user());
        }

        $this->dispatch('status-updated');
        $this->toastSuccess('Post status updated successfully.');
    }

    public function bulkPublish()
    {
        if (empty($this->selectedPosts)) {
            $this->toastError('No posts selected.');

            return;
        }

        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;

        foreach ($posts as $post) {
            if (auth()->user()->can('publish', $post)) {
                $post->update(['status' => 'published']);

                if (tallpress_setting('activity_log_enabled', true)) {
                    ActivityLog::log('bulk published post', $post, auth()->user());
                }
                $count++;
            }
        }

        $this->selectedPosts = [];
        $this->selectAll = false;

        $this->toastSuccess("{$count} posts published successfully.");
    }

    public function bulkUnpublish()
    {
        if (empty($this->selectedPosts)) {
            $this->toastError('No posts selected.');

            return;
        }

        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;

        foreach ($posts as $post) {
            if (auth()->user()->can('update', $post)) {
                $post->update(['status' => 'draft']);

                if (tallpress_setting('activity_log_enabled', true)) {
                    ActivityLog::log('bulk unpublished post', $post, auth()->user());
                }
                $count++;
            }
        }

        $this->selectedPosts = [];
        $this->selectAll = false;

        $this->toastSuccess("{$count} posts unpublished successfully.");
    }

    public function bulkDelete()
    {
        if (empty($this->selectedPosts)) {
            $this->toastError('No posts selected.');

            return;
        }

        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;

        foreach ($posts as $post) {
            if (auth()->user()->can('delete', $post)) {
                // Log activity before deletion
                if (tallpress_setting('activity_log_enabled', true)) {
                    ActivityLog::log('bulk deleted post', $post, auth()->user(), [
                        'title' => $post->title,
                    ]);
                }

                $post->delete();
                $count++;
            }
        }

        $this->selectedPosts = [];
        $this->selectAll = false;

        $this->toastSuccess("{$count} posts deleted successfully.");
    }

    public function export()
    {
        $posts = $this->getPostsQuery()->get();

        $csv = "ID,Title,Status,Author,Published At,Created At\n";
        foreach ($posts as $post) {
            $csv .= "{$post->id},\"{$post->title}\",{$post->status},{$post->author->name},{$post->published_at},{$post->created_at}\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'posts-'.now()->format('Y-m-d').'.csv');
    }

    protected function getPostsQuery()
    {
        $query = Post::with('author', 'categories', 'tags');

        // Apply filters
        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->author) {
            $query->where('author_id', $this->author);
        }

        if ($this->category) {
            $query->whereHas('categories', function ($q) {
                $q->where('category_id', $this->category);
            });
        }

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        return $query->latest();
    }

    public function render()
    {
        $posts = $this->getPostsQuery()->paginate(tallpress_setting('per_page', 15));
        $categories = Category::orderBy('name')->get();
        $authors = config('tallpress.author_model')::orderBy('name')->get();

        return view('tallpress::admin.livewire.posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'authors' => $authors,
        ])->layout('tallpress::admin.layout');
    }
}
