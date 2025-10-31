@extends('tallpress::front.layout')

@section('title', 'Blog Posts')

@section('content')
<div>
    @if($posts->count())
        {{-- Hero Section with Featured Post --}}
        @php
            $featuredPost = $posts->first();
        @endphp

        <section class="relative mb-12">
            <div class="relative h-[500px] overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-purple-700">
                @if($featuredPost->featured_image_url)
                    <img src="{{ asset($featuredPost->featured_image_url) }}"
                         alt="{{ $featuredPost->title }}"
                         class="absolute inset-0 w-full h-full object-cover opacity-40">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600"></div>
                @endif

                <div class="relative h-full flex items-end">
                    <div class="w-full p-8 md:p-12 lg:p-16">
                        <div class="max-w-4xl">
                            @if($featuredPost->categories->count())
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm text-white mb-4">
                                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    Featured
                                </span>
                            @endif

                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-4 leading-tight">
                                <a href="{{ route('tallpress.posts.show', $featuredPost->slug) }}" class="hover:text-blue-200 transition-colors">
                                    {{ $featuredPost->title }}
                                </a>
                            </h1>

                            @if($featuredPost->excerpt)
                                <p class="text-xl text-gray-100 mb-6 line-clamp-2 max-w-3xl">{{ $featuredPost->excerpt }}</p>
                            @endif

                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-200">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $featuredPost->author->name ?? 'Unknown' }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $featuredPost->published_at->format('M d, Y') }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ number_format($featuredPost->views_count) }} views
                                </div>
                            </div>

                            <a href="{{ route('tallpress.posts.show', $featuredPost->slug) }}"
                               class="inline-flex items-center mt-6 px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                                Read Article
                                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Latest Posts Section --}}
        <section class="mb-12">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Latest Articles</h2>
                    <p class="text-gray-600">Discover our newest content and insights</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts->skip(1) as $post)
                    <article class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform" wire:key="post-{{ $post->id }}">
                        @if($post->featured_image_url)
                            <div class="relative h-48 overflow-hidden bg-gray-200">
                                <img src="{{ asset($post->featured_image_url) }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        @else
                            <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                <svg class="h-16 w-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-3">
                                @if($post->categories->count())
                                    @foreach($post->categories->take(1) as $category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                @endif
                                <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                            </div>

                            <h3 class="text-xl font-bold mb-3 leading-tight">
                                <a href="{{ route('tallpress.posts.show', $post->slug) }}"
                                   class="text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ $post->title }}
                                </a>
                            </h3>

                            @if($post->excerpt)
                                <p class="text-gray-600 mb-4 line-clamp-3 text-sm">{{ $post->excerpt }}</p>
                            @endif

                            <div class="flex flex-col items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <div class="flex items-center" title="{{ $post->author->name ?? 'Unknown' }}">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-xs mr-2">
                                            {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="text-xs truncate max-w-[100px]">{{ $post->author->name ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ number_format($post->views_count) }}
                                    </div>
                                </div>

                                @if($post->tags->count())
                                    <div class="flex items-center gap-3 mt-3">
                                        @foreach($post->tags->take(2) as $tag)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

    @else
        {{-- Empty State --}}
        <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
            <div class="max-w-md mx-auto">
                <div class="h-24 w-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Posts Yet</h3>
                <p class="text-gray-600 text-lg">{{ __('tallpress::tallpress.no_posts') }}</p>
            </div>
        </div>
    @endif

    {{-- Pagination --}}
    @if($posts->hasPages())
        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
