<div>
    <div class="page-header">
        <h1 class="page-title">Comments</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Filter</label>
                    <select wire:model.live="filter" class="form-select">
                        <option value="all">All Comments</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Search</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search comments..." class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedComments) > 0)
        <div class="card mb-4 bg-blue-50">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <span class="font-medium">{{ count($selectedComments) }} comments selected</span>
                    <div class="space-x-2">
                        <button wire:click="bulkApprove" class="btn btn-sm btn-success">Approve</button>
                        <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete these comments? This action cannot be undone." class="btn btn-sm btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Comments Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" wire:model.live="selectAll">
                        </th>
                        <th>Author</th>
                        <th>Content</th>
                        <th>Post</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                        <tr>
                            <td>
                                <input type="checkbox" wire:model.live="selectedComments" value="{{ $comment->id }}">
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $comment->author_name }}</div>
                                    <div class="text-sm text-gray-600">{{ $comment->author_email }}</div>
                                </div>
                            </td>
                            <td>{{ Str::limit($comment->body, 60) }}</td>
                            <td>
                                @if($comment->post)
                                    <a href="{{ route('tallpress.posts.show', $comment->post->slug) }}" target="_blank" class="text-blue-600">
                                        {{ Str::limit($comment->post->title, 30) }}
                                    </a>
                                @else
                                    <span class="text-gray-500">[deleted post]</span>
                                @endif
                            </td>
                            <td>
                                @if($comment->approved)
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="flex space-x-2">
                                    @if(!$comment->approved)
                                        <button wire:click="approveComment({{ $comment->id }})" class="btn btn-xs btn-success">
                                            Approve
                                        </button>
                                    @else
                                        <button wire:click="rejectComment({{ $comment->id }})" class="btn btn-xs btn-warning">
                                            Reject
                                        </button>
                                    @endif
                                    <button wire:click="deleteComment({{ $comment->id }})"
                                            wire:confirm="Are you sure you want to delete this comment? This action cannot be undone."
                                            class="btn btn-xs btn-danger">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500">No comments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($comments->hasPages())
            <div class="card-footer">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
</div>
