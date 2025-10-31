<div class="tallpress-search-component">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('tallpress::tallpress.all_posts') }}</h1>
        <p class="text-gray-600">Discover our latest articles and insights</p>
    </div>

    {{-- Search and Filters --}}
    @if($searchEnabled)
        {{-- Active Filters Display --}}
        @if($query || $category || $tag)
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live.debounce.500ms="query"
                                placeholder="Search posts..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <button
                        wire:click="clearSearch"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm text-gray-600">Active filters:</span>

                        @if($query)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Search: {{ $query }}
                                <button wire:click="$set('query', '')" class="ml-2 hover:text-blue-900">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif

                        @if($category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Category: {{ $category }}
                                <button wire:click="$set('category', null)" class="ml-2 hover:text-green-900">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif

                        @if($tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Tag: {{ $tag }}
                                <button wire:click="$set('tag', null)" class="ml-2 hover:text-purple-900">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Posts List --}}
    <div class="space-y-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200" wire:key="post-{{ $post->id }}">
                <div class="flex flex-col md:flex-row">
                    @if($post->featured_image_url)
                    <div class="md:w-64 md:flex-shrink-0">
                        <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}" class="h-48 md:h-full w-full object-cover">
                    </div>
                    @endif

                    <div class="p-6 flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            @if($post->categories->count())
                                @foreach($post->categories->take(1) as $postCategory)
                                    <button
                                        wire:click="$set('category', '{{ $postCategory->slug }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors"
                                    >
                                        {{ $postCategory->name }}
                                    </button>
                                @endforeach
                            @endif
                            <span class="text-sm text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        </div>

                        <h2 class="text-2xl font-bold mb-3">
                            <a href="{{ route('tallpress.posts.show', $post->slug) }}" class="text-gray-900 hover:text-blue-600 transition-colors" wire:navigate>
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if($post->excerpt)
                            <p class="text-gray-700 mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $post->author->name ?? 'Unknown' }}
                            </div>

                            @if($post->tags->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach($post->tags->take(3) as $postTag)
                                        <button
                                            wire:click="$set('tag', '{{ $postTag->slug }}')"
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                        >
                                            #{{ $postTag->name }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600 text-lg">
                    @if($query || $category || $tag)
                        No posts found matching your filters.
                    @else
                        {{ __('tallpress::tallpress.no_posts') }}
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($posts->hasPages())
        <div class="mt-8">
            {{ $posts->links('tallpress::components.pagination-links') }}
        </div>
    @endif
</div>
