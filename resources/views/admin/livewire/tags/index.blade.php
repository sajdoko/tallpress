<div>
    <div class="page-header">
        <h1 class="page-title">Tags</h1>
        <button wire:click="createTag" class="btn btn-primary">New Tag</button>
    </div>

    <!-- Search -->
    <div class="card mb-6">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search tags..." class="form-input">
        </div>
    </div>

    <!-- Form Modal -->
    @if($showForm)
        <div class="card mb-6 bg-blue-50">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">{{ $editingTag ? 'Edit Tag' : 'New Tag' }}</h3>
                <button wire:click="cancelForm" class="btn btn-sm btn-secondary">Cancel</button>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="saveTag">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Name *</label>
                            <input type="text" wire:model="name" class="form-input" required>
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" wire:model="slug" class="form-input">
                            @error('slug') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            {{ $editingTag ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Tags Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Posts Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tags as $tag)
                        <tr>
                            <td class="font-medium">{{ $tag->name }}</td>
                            <td><code>{{ $tag->slug }}</code></td>
                            <td>{{ $tag->posts_count }}</td>
                            <td>
                                <div class="flex space-x-2">
                                    <button wire:click="editTag({{ $tag->id }})" class="btn btn-xs btn-secondary">
                                        Edit
                                    </button>
                                    <button wire:click="deleteTag({{ $tag->id }})"
                                            wire:confirm="Are you sure you want to delete this tag? This action cannot be undone."
                                            class="btn btn-xs btn-danger">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500">No tags found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tags->hasPages())
            <div class="card-footer">
                {{ $tags->links() }}
            </div>
        @endif
    </div>
</div>
