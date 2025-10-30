<div x-data="{ open: false }"
     @open-media-picker.window="open = true"
     @close-media-picker.window="open = false">

    <!-- Modal Backdrop -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
         style="display: none;"
         @click="open = false">
    </div>

    <!-- Modal -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col" @click.stop>

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Media Library</h3>
                    <button @click="open = false" wire:click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="border-b">
                    <nav class="flex space-x-4 px-4">
                        <button
                            wire:click="switchMode('library')"
                            class="py-3 px-2 border-b-2 font-medium text-sm {{ $mode === 'library' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Media Library
                        </button>
                        <button
                            wire:click="switchMode('upload')"
                            class="py-3 px-2 border-b-2 font-medium text-sm {{ $mode === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Upload Files
                        </button>
                    </nav>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-4">
                    @if($mode === 'library')
                        <!-- Search -->
                        <div class="mb-4">
                            <input type="text"
                                   wire:model.live.debounce.500ms="search"
                                   placeholder="Search files..."
                                   class="form-input w-full">
                        </div>

                        <!-- Media Grid -->
                        @if($media->count() > 0)
                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                @foreach($media as $item)
                                    <div wire:click="selectMedia({{ $item->id }})"
                                         class="relative border-2 rounded-lg p-2 cursor-pointer transition {{ $selectedMedia?->id === $item->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">

                                        <div class="aspect-square bg-gray-100 rounded overflow-hidden mb-2">
                                            <img src="{{ $item->url }}"
                                                 alt="{{ $item->filename }}"
                                                 class="w-full h-full object-cover">
                                        </div>

                                        @if($selectedMedia?->id === $item->id)
                                            <div class="absolute top-3 right-3 bg-blue-500 text-white rounded-full p-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <p class="text-xs text-gray-600 truncate" title="{{ $item->filename }}">
                                            {{ $item->filename }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $media->links() }}
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2">No media files found.</p>
                            </div>
                        @endif

                    @else
                        <!-- Upload Form -->
                        <div class="max-w-xl mx-auto">
                            <form wire:submit.prevent="uploadFiles">
                                <div class="space-y-4">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="mt-4">
                                            <label class="cursor-pointer">
                                                <span class="btn btn-primary">Choose Files</span>
                                                <input type="file" wire:model="files" multiple accept="image/*" class="hidden">
                                            </label>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">or drag and drop</p>
                                        <p class="text-xs text-gray-400">PNG, JPG, GIF, WEBP up to 10MB</p>
                                    </div>

                                    @error('files.*')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror

                                    <div wire:loading wire:target="files" class="text-sm text-gray-600 text-center">
                                        Processing files...
                                    </div>

                                    @if(!empty($files))
                                        <div class="text-center">
                                            <p class="text-sm text-gray-600">{{ count($files) }} file(s) selected</p>
                                            <button type="submit" class="btn btn-primary mt-2" wire:loading.attr="disabled">
                                                <span wire:loading.remove wire:target="uploadFiles">Upload Files</span>
                                                <span wire:loading wire:target="uploadFiles">Uploading...</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Footer with Selected Media Details -->
                @if($selectedMedia && $mode === 'library')
                    <div class="border-t p-4 bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-sm mb-1">{{ $selectedMedia->filename }}</h4>
                                <div class="text-xs text-gray-500 space-y-1">
                                    <p>{{ number_format($selectedMedia->size / 1024, 1) }} KB</p>
                                    @if($selectedMedia->width && $selectedMedia->height)
                                        <p>{{ $selectedMedia->width }} × {{ $selectedMedia->height }} pixels</p>
                                    @endif
                                    <p>Uploaded {{ $selectedMedia->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <button wire:click="insertSelected" class="btn btn-primary">
                                Insert into Post
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
