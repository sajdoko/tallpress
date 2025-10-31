<div>
    @if($commentsEnabled)
        <div class="border-t border-gray-200 p-6 md:p-8 lg:p-12 bg-gray-50">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                {{ __('tallpress::tallpress.comments') }} ({{ $post->approvedComments->count() }})
            </h2>

            {{-- Existing Comments --}}
            @if($post->approvedComments->count())
                <div class="space-y-4 mb-8">
                    @foreach($post->approvedComments as $comment)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" wire:key="comment-{{ $comment->id }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <span class="text-blue-600 font-semibold text-sm">
                                            {{ strtoupper(substr($comment->author_name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $comment->author_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $comment->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-700 leading-relaxed">{{ $comment->body }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Success/Error Messages --}}
            @if (session()->has('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Comment Form --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave a Comment</h3>

                <form wire:submit="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input
                                type="text"
                                id="author_name"
                                wire:model="author_name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('author_name') border-red-500 @enderror"
                            >
                            @error('author_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="author_email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input
                                type="email"
                                id="author_email"
                                wire:model="author_email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('author_email') border-red-500 @enderror"
                            >
                            @error('author_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Comment *</label>
                        <textarea
                            id="body"
                            wire:model="body"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('body') border-red-500 @enderror"
                        ></textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($requireApproval)
                        <p class="text-sm text-gray-600">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Your comment will be reviewed before being published.
                        </p>
                    @endif

                    <div>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg wire:loading.remove class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <svg wire:loading class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Post Comment</span>
                            <span wire:loading>Posting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
