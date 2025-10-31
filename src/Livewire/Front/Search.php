<?php

namespace Sajdoko\TallPress\Livewire\Front;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Models\Post;

class Search extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public string $query = '';

    #[Url(keep: true)]
    public ?string $category = null;

    #[Url(keep: true)]
    public ?string $tag = null;

    public bool $searchEnabled = true;

    protected $queryString = [
        'query' => ['as' => 'q', 'except' => ''],
        'category' => ['except' => null],
        'tag' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->searchEnabled = tallpress_setting('search_enabled', true);

        // Get values from request if present
        $this->query = request('q', $this->query);
        $this->category = request('category', $this->category);
        $this->tag = request('tag', $this->tag);
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->reset(['query', 'category', 'tag']);
        $this->resetPage();
    }

    public function render()
    {
        $queryBuilder = Post::with(['author', 'categories', 'tags'])
            ->published()
            ->latest('published_at');

        // Filter by category
        if ($this->category) {
            $queryBuilder->whereHas('categories', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        // Filter by tag
        if ($this->tag) {
            $queryBuilder->whereHas('tags', function ($q) {
                $q->where('slug', $this->tag);
            });
        }

        // Apply search if enabled and query is provided
        if ($this->searchEnabled && $this->query) {
            $queryBuilder->search($this->query);
        }

        $posts = $queryBuilder->paginate(tallpress_setting('per_page', 15));

        return view('tallpress::front.livewire.search', [
            'posts' => $posts,
        ])->layout('tallpress::front.layout', [
            'title' => tallpress_setting('tallpress_title', 'Blog Posts'),
        ]);
    }
}
