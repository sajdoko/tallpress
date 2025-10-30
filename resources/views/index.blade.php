@extends('tallpress::layout')

@section('title', tallpress_setting('tallpress_title', 'Blog Posts'))

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('tallpress::tallpress.all_posts') }}</h1>
        <p class="text-gray-600">Discover our latest articles and insights</p>
    </div>

    <div class="space-y-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="flex flex-col md:flex-row">
                    @if($post->featured_image_url)
                    <div class="md:w-64 md:flex-shrink-0">
                        <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}" class="h-48 md:h-full w-full object-cover">
                    </div>
                    @endif
                    
                    <div class="p-6 flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            @if($post->categories->count())
                                @foreach($post->categories->take(1) as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            @endif
                            <span class="text-sm text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        </div>

                        <h2 class="text-2xl font-bold mb-3">
                            <a href="{{ route('tallpress.posts.show', $post->slug) }}" class="text-gray-900 hover:text-blue-600 transition-colors">
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
                                    @foreach($post->tags->take(3) as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            #{{ $tag->name }}
                                        </span>
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
                <p class="text-gray-600 text-lg">{{ __('tallpress::tallpress.no_posts') }}</p>
            </div>
        @endforelse
    </div>

    @if($posts->hasPages())
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
    @endif
</div>
@endsection
