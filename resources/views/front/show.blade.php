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
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>{{ number_format($post->views_count) }} {{ $post->views_count === 1 ? 'view' : 'views' }}</span>
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

        {{-- Social Share Buttons --}}
        @if(tallpress_setting('social_share_enabled', false))
        <div class="border-t border-gray-200 pt-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this post</h3>
            <div class="flex flex-wrap gap-3">
                @if(tallpress_setting('social_share_facebook', true))
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-[#1877F2] text-white rounded-lg hover:bg-[#166FE5] transition-colors"
                   aria-label="Share on Facebook">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                @endif

                @if(tallpress_setting('social_share_twitter', true))
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-[#1DA1F2] text-white rounded-lg hover:bg-[#1A94DA] transition-colors"
                   aria-label="Share on Twitter">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                    Twitter
                </a>
                @endif

                @if(tallpress_setting('social_share_linkedin', true))
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-[#0A66C2] text-white rounded-lg hover:bg-[#095196] transition-colors"
                   aria-label="Share on LinkedIn">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    LinkedIn
                </a>
                @endif

                @if(tallpress_setting('social_share_reddit', false))
                <a href="https://reddit.com/submit?url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-[#FF4500] text-white rounded-lg hover:bg-[#E63E00] transition-colors"
                   aria-label="Share on Reddit">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/>
                    </svg>
                    Reddit
                </a>
                @endif

                @if(tallpress_setting('social_share_whatsapp', false))
                <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-[#25D366] text-white rounded-lg hover:bg-[#1EBE57] transition-colors"
                   aria-label="Share on WhatsApp">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    WhatsApp
                </a>
                @endif

                @if(tallpress_setting('social_share_email', true))
                <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(($post->excerpt ?: 'Check out this post: ' . $post->title) . "\n\n" . url()->current()) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                   aria-label="Share via Email">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email
                </a>
                @endif
            </div>
        </div>
        @endif
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
