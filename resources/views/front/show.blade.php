@extends('tallpress::front.layout')

@section('title', $post->seo_title ?: $post->title)

@section('meta_description', $post->meta_description ?: $post->excerpt)

@if($post->featured_image_url)
    @section('og_image', asset($post->featured_image_url))
@endif

@section('og_type', 'article')

@section('content')
<article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    @if($post->featured_image_url)
        <div class="w-full h-64 md:h-96 overflow-hidden">
            <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="p-6 md:p-8 lg:p-12">
        {{-- Post Header --}}
        <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center gap-2 mb-4">
                @if($post->categories->count())
                    @foreach($post->categories as $category)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $category->name }}
                        </span>
                    @endforeach
                @endif
                @if($post->status === 'draft')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ __('tallpress::tallpress.draft') }}
                    </span>
                @endif
            </div>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="font-medium">{{ $post->author->name ?? 'Unknown' }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            @if($post->tags->count())
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($post->tags as $tag)
                        <a href="{{ route('tallpress.posts.index') }}?tag={{ $tag->slug }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Post Content --}}
        <div class="prose prose-lg max-w-none mb-8">
            {!! $post->body_html !!}
        </div>
    </div>

    {{-- Comments Section - Livewire Component --}}
    @livewire('tallpress-comments', ['post' => $post])
</article>

@if($post->schema_markup)
    @push('schema')
        <script type="application/ld+json">
            {!! json_encode($post->schema_markup, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endif

@push('styles')
<style>
    /* Custom prose styles for post content */
    .prose {
        color: #374151;
    }
    .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
        color: #111827;
        font-weight: 700;
        margin-top: 2em;
        margin-bottom: 1em;
        line-height: 1.3;
    }
    .prose h1 { font-size: 2.25em; }
    .prose h2 { font-size: 1.875em; }
    .prose h3 { font-size: 1.5em; }
    .prose h4 { font-size: 1.25em; }
    .prose p {
        margin-bottom: 1.25em;
        line-height: 1.75;
    }
    .prose a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
    }
    .prose a:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .prose ul, .prose ol {
        margin-top: 1.25em;
        margin-bottom: 1.25em;
        padding-left: 1.625em;
    }
    .prose li {
        margin-top: 0.5em;
        margin-bottom: 0.5em;
    }

    /* Quill indented lists support */
    .prose li[data-list] {
        list-style-type: none;
        padding-left: 1.5em;
        position: relative;
    }

    .prose li[data-list="bullet"]::before {
        content: "•";
        position: absolute;
        left: 0;
    }

    .prose li[data-list="ordered"] {
        counter-increment: list-0;
    }

    .prose li[data-list="ordered"]::before {
        content: counter(list-0) ". ";
        position: absolute;
        left: 0;
    }

    /* Indent level 1 */
    .prose li[data-list].ql-indent-1 {
        padding-left: 4.5em;
    }

    /* Indent level 2 */
    .prose li[data-list].ql-indent-2 {
        padding-left: 7.5em;
    }

    /* Indent level 3 */
    .prose li[data-list].ql-indent-3 {
        padding-left: 10.5em;
    }

    /* Indent level 4 */
    .prose li[data-list].ql-indent-4 {
        padding-left: 13.5em;
    }

    /* Indent level 5 */
    .prose li[data-list].ql-indent-5 {
        padding-left: 16.5em;
    }

    /* Indent level 6 */
    .prose li[data-list].ql-indent-6 {
        padding-left: 19.5em;
    }

    /* Indent level 7 */
    .prose li[data-list].ql-indent-7 {
        padding-left: 22.5em;
    }

    /* Indent level 8 */
    .prose li[data-list].ql-indent-8 {
        padding-left: 25.5em;
    }

    /* Indent level 9 */
    .prose li[data-list].ql-indent-9 {
        padding-left: 28.5em;
    }
    .prose blockquote {
        border-left: 4px solid #e5e7eb;
        padding-left: 1em;
        font-style: italic;
        color: #6b7280;
        margin: 1.5em 0;
    }
    .prose code {
        background-color: #f3f4f6;
        color: #dc2626;
        padding: 0.2em 0.4em;
        border-radius: 0.25rem;
        font-size: 0.875em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .prose pre {
        background-color: #1f2937;
        color: #f3f4f6;
        padding: 1.25em;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5em 0;
    }
    .prose pre code {
        background-color: transparent;
        color: #f3f4f6;
        padding: 0;
    }
    .prose img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 2em 0;
    }
    .prose table {
        width: 100%;
        margin: 1.5em 0;
        border-collapse: collapse;
    }
    .prose th, .prose td {
        border: 1px solid #e5e7eb;
        padding: 0.75em;
        text-align: left;
    }
    .prose th {
        background-color: #f9fafb;
        font-weight: 600;
    }
    .prose hr {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 3em 0;
    }
</style>
@endpush
@endsection
