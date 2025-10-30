<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Blog Admin') - {{ config('app.name', 'Laravel') }}</title>

    {{-- Load precompiled tallpress assets --}}
    <link href="{{ asset('/vendor/tallpress/css/tallpress-admin.css') }}" rel="stylesheet" onerror="alert('tallpress-admin.css failed to load. Please refresh the page, re-publish Blog assets with: php artisan vendor:publish --tag=tallpress-assets, or fix routing for vendor assets.')">

    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl">
            <div class="flex flex-col h-full">
                {{-- Sidebar Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-700">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="ml-3 text-xl font-bold">Blog Admin</span>
                    </div>
                    <button onclick="toggleSidebar()" class="lg:hidden p-1 rounded-md hover:bg-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Sidebar Navigation --}}
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    <a href="{{ route('tallpress.admin.dashboard') }}" class="nav-link {{ request()->routeIs('tallpress.admin.dashboard') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('tallpress.admin.posts.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.posts.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Posts</span>
                    </a>

                    <a href="{{ route('tallpress.admin.categories.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.categories.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>Categories</span>
                    </a>

                    <a href="{{ route('tallpress.admin.tags.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.tags.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>Tags</span>
                    </a>

                    @if(tallpress_setting('comments_enabled', true))
                    @can('moderateComments', \Sajdoko\TallPress\Models\Post::class)
                    <a href="{{ route('tallpress.admin.comments.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.comments.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span>Comments</span>
                    </a>
                    @endcan
                    @endif

                    @can('manageMedia', \Sajdoko\TallPress\Models\Post::class)
                    <a href="{{ route('tallpress.admin.media.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.media.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Media</span>
                    </a>
                    @endcan

                    @can('manageUsers', \Sajdoko\TallPress\Models\Post::class)
                    <a href="{{ route('tallpress.admin.users.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.users.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Users</span>
                    </a>
                    @endcan

                    @can('manageSettings', \Sajdoko\TallPress\Models\Post::class)
                    <a href="{{ route('tallpress.admin.settings.index') }}" class="nav-link {{ request()->routeIs('tallpress.admin.settings.*') ? 'active' : '' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Settings</span>
                    </a>
                    @endcan
                </nav>

                {{-- Sidebar Footer --}}
                <div class="px-4 py-4 border-t border-gray-700">
                    <a href="{{ route('tallpress.posts.index') }}" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        View Blog
                    </a>
                </div>
            </div>
        </aside>

        {{-- Sidebar Overlay (Mobile) --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col lg:ml-64">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <div class="flex-1 lg:flex-none"></div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-sm text-gray-700">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center mr-3">
                                    <span class="text-white font-semibold text-sm">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                </div>
                                <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    @if (session('success'))
                        <div class="alert alert-success mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-error mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error mb-6">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    <script type="module" src="{{ asset('vendor/tallpress/js/tallpress-admin.js') }}?v={{ file_exists(public_path('vendor/tallpress/js/tallpress-admin.js')) ? filemtime(public_path('vendor/tallpress/js/tallpress-admin.js')) : time() }}" onerror="alert('tallpress-admin.js failed to load. Please refresh the page, re-publish Blog assets with: php artisan vendor:publish --tag=tallpress-assets, or fix routing for vendor assets.')"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

    {{-- Toast Notifications --}}
    <x-tallpress::toast />

    @stack('scripts')
</body>
</html>
