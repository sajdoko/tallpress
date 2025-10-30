<div>
    <div class="page-header">
        <h1 class="page-title">Posts</h1>
        @can('create', \Sajdoko\TallPress\Models\Post::class)
            <a href="{{ route('tallpress.admin.posts.create') }}" class="btn btn-primary">
                Create Post
            </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Status</label>
                    <select wire:model.live="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="published">Published</option>
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Author</label>
                    <select wire:model.live="author" class="form-select">
                        <option value="">All Authors</option>
                        @foreach($authors as $authorItem)
                            <option value="{{ $authorItem->id }}">{{ $authorItem->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Category</label>
                    <select wire:model.live="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Search</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search posts..." class="form-input">
                </div>

                <div>
                    <label class="form-label">Date From</label>
                    <input type="date" wire:model.live="dateFrom" class="form-input">
                </div>

                <div>
                    <label class="form-label">Date To</label>
                    <input type="date" wire:model.live="dateTo" class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedPosts) > 0)
        <div class="card mb-4 bg-blue-50">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <span class="font-medium">{{ count($selectedPosts) }} posts selected</span>
                    <div class="space-x-2">
                        <button wire:click="bulkPublish" class="btn btn-sm btn-success">Publish</button>
                        <button wire:click="bulkUnpublish" class="btn btn-sm btn-warning">Unpublish</button>
                        <button wire:click="bulkDelete" wire:confirm="Are you sure?" class="btn btn-sm btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Posts Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" wire:model.live="selectAll">
                        </th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Categories</th>
                        <th>Status</th>
                        <th>Published At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>
                                <input type="checkbox" wire:model.live="selectedPosts" value="{{ $post->id }}">
                            </td>
                            <td>
                                @can('update', $post)
                                    <a href="{{ route('tallpress.admin.posts.edit', $post) }}" class="font-medium">
                                        {{ $post->title }}
                                    </a>
                                @else
                                    {{ $post->title }}
                                @endcan
                            </td>
                            <td>{{ $post->author->name }}</td>
                            <td>
                                @foreach($post->categories as $category)
                                    <span class="badge">{{ $category->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge badge-{{ $post->status }}">{{ ucfirst($post->status) }}</span>
                            </td>
                            <td>{{ $post->published_at?->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                <div class="flex space-x-2">
                                    @can('update', $post)
                                        <a href="{{ route('tallpress.admin.posts.edit', $post) }}" class="btn btn-xs btn-secondary">Edit</a>
                                    @endcan
                                    @if($post->status !== 'published' && auth()->user()->can('publish', $post))
                                        <button wire:click="updateStatus({{ $post->id }}, 'published')" class="btn btn-xs btn-success">Publish</button>
                                    @endif
                                    @can('delete', $post)
                                        <button wire:click="deletePost({{ $post->id }})" wire:confirm="Are you sure?" class="btn btn-xs btn-danger">Delete</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="card-footer">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
