<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', tallpress_setting('tallpress_title', 'Blog')) - {{ config('app.name') }}</title>

    {{-- SEO Meta Tags --}}
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @else
        <meta name="description" content="{{ tallpress_setting('tallpress_description', 'A modern tallpress platform') }}">
    @endif

    @stack('schema')

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', tallpress_setting('tallpress_title', 'Blog')) - {{ config('app.name') }}">
    @hasSection('meta_description')
        <meta property="og:description" content="@yield('meta_description')">
    @else
        <meta property="og:description" content="{{ tallpress_setting('tallpress_description', 'A modern tallpress platform') }}">
    @endif
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', tallpress_setting('tallpress_title', 'Blog')) - {{ config('app.name') }}">
    @hasSection('meta_description')
        <meta name="twitter:description" content="@yield('meta_description')">
    @else
        <meta name="twitter:description" content="{{ tallpress_setting('tallpress_description', 'A modern tallpress platform') }}">
    @endif
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @endif

    {{-- Load tallpress assets from package public directory --}}
    <link href="{{ asset('vendor/tallpress/css/tallpress-frontend.css') }}" rel="stylesheet">

    {{-- Livewire Styles --}}
    @livewireStyles

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('tallpress.posts.index') }}" class="flex items-center">
                        @if(tallpress_setting('tallpress_logo'))
                            <img src="{{ asset(tallpress_setting('tallpress_logo')) }}" alt="{{ tallpress_setting('tallpress_title', config('app.name')) }}" class="h-8 w-auto">
                        @else
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        @endif
                        <span class="ml-3 text-xl font-bold text-gray-900">{{ tallpress_setting('tallpress_title', config('app.name')) }}</span>
                    </a>
                </div>
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('tallpress.posts.index') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">{{ __('tallpress::tallpress.home') }}</a>
                </nav>
                <button class="md:hidden p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Container with Sidebar Layout --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Main Content --}}
            <main class="flex-1 min-w-0">
                @if(session('success'))
                    <div class="alert alert-success mb-6">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error mb-6">{{ session('error') }}</div>
                @endif

                @yield('content')
                {{ $slot ?? '' }}
            </main>

            {{-- Sidebar --}}
            <aside class="lg:w-80 flex-shrink-0">
                <div class="space-y-6">
                    {{-- Search Widget - Livewire Component --}}
                    @livewire('tallpress-search-widget')

                    {{-- Recent Posts Widget --}}
                    @php
                        $recentPosts = \Sajdoko\TallPress\Models\Post::published()
                            ->latest('published_at')
                            ->limit(5)
                            ->get(['id', 'title', 'slug', 'published_at']);
                    @endphp
                    @if($recentPosts->count())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Posts</h3>
                        <ul class="space-y-3">
                            @foreach($recentPosts as $recentPost)
                            <li>
                                <a href="{{ route('tallpress.posts.show', $recentPost->slug) }}" class="text-gray-700 hover:text-blue-600 text-sm line-clamp-2 transition-colors">
                                    {{ $recentPost->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">{{ $recentPost->published_at->format('M d, Y') }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Categories Widget --}}
                    @php
                        $categories = \Sajdoko\TallPress\Models\Category::withCount('posts')
                            ->orderBy('name')
                            ->get()
                            ->filter(fn($cat) => $cat->posts_count > 0);
                    @endphp
                    @if($categories->count())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Categories</h3>
                        <ul class="space-y-2">
                            @foreach($categories as $category)
                            <li class="flex items-center justify-between text-sm">
                                <a href="{{ route('tallpress.posts.index') }}?category={{ $category->slug }}" class="text-gray-700 hover:text-blue-600 transition-colors">
                                    {{ $category->name }}
                                </a>
                                <span class="text-gray-500 text-xs">{{ $category->posts_count }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Tags Widget --}}
                    @php
                        $tags = \Sajdoko\TallPress\Models\Tag::withCount('posts')
                            ->orderBy('posts_count', 'desc')
                            ->limit(15)
                            ->get()
                            ->filter(fn($tag) => $tag->posts_count > 0);
                    @endphp
                    @if($tags->count())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                            <a href="{{ route('tallpress.posts.index') }}?tag={{ $tag->slug }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                {{ $tag->name }}
                                <span class="ml-1.5 text-gray-500">{{ $tag->posts_count }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </aside>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- About Section --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">About</h3>
                    <p class="text-gray-600 text-sm">
                        {{ tallpress_setting('tallpress_description', config('app.name') . ' - A modern tallpress platform built with Laravel.') }}
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('tallpress.posts.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Home</a></li>
                        @if(auth()->check() && auth()->user()->can('access-tallpress-admin'))
                        <li><a href="{{ route('tallpress.admin.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Admin Dashboard</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Social Links --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors" aria-label="Twitter">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors" aria-label="GitHub">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 text-center">
                    &copy; {{ date('Y') }} {{ tallpress_setting('tallpress_title', config('app.name')) }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script type="module" src="{{ asset('vendor/tallpress/js/tallpress-frontend.js') }}"></script>

    {{-- Livewire Scripts --}}
    @livewireScripts

    @stack('scripts')
</body>
</html>
