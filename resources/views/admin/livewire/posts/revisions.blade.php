<div>
    <div class="page-header">
        <h1 class="page-title">Revisions for: {{ $post->title }}</h1>
        <div class="flex space-x-2">
            @if($revisions->total() > 0)
                <button wire:click="deleteAllRevisions"
                        wire:confirm="Are you sure you want to delete ALL {{ $revisions->total() }} revision(s)? This action cannot be undone."
                        class="btn btn-danger">
                    Delete All ({{ $revisions->total() }})
                </button>
            @endif
            <a href="{{ route('tallpress.admin.posts.edit', $post) }}" class="btn btn-secondary">Back to Post</a>
        </div>
    </div>

    @if($showComparison && $selectedRevision)
        <div class="card mb-6">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">Revision Comparison</h3>
                <button wire:click="closeComparison" class="btn btn-sm btn-secondary">Close</button>
            </div>
            <div class="card-body space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <strong class="text-blue-900">Revision Date:</strong>
                            <span class="text-blue-800">{{ $selectedRevision->created_at->format('F d, Y \a\t H:i') }}</span>
                            @if($selectedRevision->user)
                                <span class="text-blue-700"> by {{ $selectedRevision->user->name }}</span>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="restoreRevision({{ $selectedRevision->id }})"
                                    wire:confirm="Are you sure you want to restore this revision? A new revision of current content will be created first."
                                    class="btn btn-primary">
                                Restore This Revision
                            </button>
                            <button wire:click="deleteRevision({{ $selectedRevision->id }})"
                                    wire:confirm="Are you sure you want to delete this revision? This action cannot be undone."
                                    class="btn btn-danger">
                                Delete Revision
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Title Comparison -->
                <div>
                    <h4 class="font-bold text-gray-700 mb-3">Title</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Current</div>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded">
                                <p class="font-semibold">{{ $post->title }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Revision</div>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded">
                                <p class="font-semibold {{ $post->title !== $selectedRevision->title ? 'text-blue-700' : '' }}">
                                    {{ $selectedRevision->title }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Excerpt Comparison -->
                @if($post->excerpt || $selectedRevision->excerpt)
                    <div>
                        <h4 class="font-bold text-gray-700 mb-3">Excerpt</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Current</div>
                                <div class="bg-gray-50 border border-gray-200 p-3 rounded">
                                    <p class="text-sm">{{ $post->excerpt ?: 'No excerpt' }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Revision</div>
                                <div class="bg-blue-50 border border-blue-200 p-3 rounded">
                                    <p class="text-sm {{ $post->excerpt !== $selectedRevision->excerpt ? 'text-blue-700' : '' }}">
                                        {{ $selectedRevision->excerpt ?: 'No excerpt' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Body Comparison -->
                <div>
                    <h4 class="font-bold text-gray-700 mb-3">Content</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Current ({{ Str::length($post->body) }} characters)</div>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded max-h-96 overflow-y-auto">
                                <div class="prose prose-sm max-w-none">{!! Str::limit($post->body, 1000) !!}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Revision ({{ Str::length($selectedRevision->body) }} characters)</div>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded max-h-96 overflow-y-auto">
                                <div class="prose prose-sm max-w-none {{ $post->body !== $selectedRevision->body ? 'text-blue-700' : '' }}">
                                    {!! Str::limit($selectedRevision->body, 1000) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Revision History ({{ $revisions->total() }} total)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Author</th>
                        <th>Title</th>
                        <th>Changes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revisions as $revision)
                        <tr class="{{ $selectedRevision?->id === $revision->id ? 'bg-blue-50' : '' }}">
                            <td class="whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $revision->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $revision->created_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td>
                                @if($revision->user)
                                    <span class="text-sm text-gray-900">{{ $revision->user->name }}</span>
                                @else
                                    <span class="text-sm text-gray-400">Unknown</span>
                                @endif
                            </td>
                            <td>
                                <div class="max-w-xs">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $revision->title }}</p>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-gray-600">
                                    <div class="flex items-center space-x-2">
                                        @if($post->title !== $revision->title)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Title
                                            </span>
                                        @endif
                                        @if($post->body !== $revision->body)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Body
                                            </span>
                                        @endif
                                        @if($post->excerpt !== $revision->excerpt)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                Excerpt
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="viewRevision({{ $revision->id }})" class="btn btn-xs btn-secondary">
                                        <span class="hidden sm:inline">View</span>
                                        <span class="sm:hidden">👁️</span>
                                    </button>
                                    <button wire:click="restoreRevision({{ $revision->id }})"
                                            wire:confirm="Are you sure you want to restore this revision? A new revision of current content will be created first."
                                            class="btn btn-xs btn-primary">
                                        <span class="hidden sm:inline">Restore</span>
                                        <span class="sm:hidden">↻</span>
                                    </button>
                                    <button wire:click="deleteRevision({{ $revision->id }})"
                                            wire:confirm="Are you sure you want to delete this revision? This cannot be undone."
                                            class="btn btn-xs btn-danger">
                                        <span class="hidden sm:inline">Delete</span>
                                        <span class="sm:hidden">🗑️</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p>No revisions found.</p>
                                    <p class="text-sm text-gray-400 mt-1">Revisions are created automatically when you update a post.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($revisions->hasPages())
            <div class="card-footer">
                {{ $revisions->links() }}
            </div>
        @endif
    </div>
</div>
