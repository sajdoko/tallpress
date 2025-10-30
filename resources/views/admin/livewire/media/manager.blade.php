<div>
    <div class="page-header">
        <h1 class="page-title">Media Manager</h1>
    </div>

    <!-- Upload Form -->
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Upload Files</h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="uploadFiles">
                <div class="space-y-4">
                    <div>
                        <input type="file" wire:model="files" multiple accept="image/*" class="form-input">
                        @error('files.*') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div wire:loading wire:target="files" class="text-sm text-gray-600">
                        Processing files...
                    </div>

                    @if(!empty($files))
                        <div>
                            <p class="text-sm text-gray-600">{{ count($files) }} file(s) selected</p>
                        </div>
                    @endif

                    <div>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="uploadFiles">Upload</span>
                            <span wire:loading wire:target="uploadFiles">Uploading...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search -->
    <div class="card mb-6">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search files..." class="form-input">
        </div>
    </div>

    <!-- Media Grid -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Media Files ({{ $media->total() }})</h3>
        </div>
        <div class="card-body">
            @if($media->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($media as $item)
                        <div class="border rounded-lg p-2">
                            <div class="aspect-square bg-gray-100 rounded overflow-hidden mb-2">
                                <img src="{{ $item->url }}" alt="{{ $item->filename }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                            <p class="text-xs text-gray-600 truncate" title="{{ $item->filename }}">{{ $item->filename }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($item->size / 1024, 1) }} KB</p>
                            @if($item->width && $item->height)
                                <p class="text-xs text-gray-500">{{ $item->width }}×{{ $item->height }}</p>
                            @endif
                            <div class="mt-2 flex space-x-1">
                                <button onclick="copyToClipboard('{{ $item->url }}')" class="btn btn-xs btn-secondary flex-1">
                                    Copy URL
                                </button>
                                <button wire:click="deleteMedia({{ $item->id }})"
                                        wire:confirm="Are you sure you want to delete this media file? This action cannot be undone."
                                        class="btn btn-xs btn-danger">
                                    Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $media->links() }}
                </div>
            @else
                <p class="text-center text-gray-500">No media files found.</p>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard!');
            });
        }
    </script>
    @endpush
</div>
