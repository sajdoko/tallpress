<?php

use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

it('creates tallpress_posts table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_posts'))->toBeTrue();
});

it('creates tallpress_categories table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_categories'))->toBeTrue();
});

it('creates tallpress_tags table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_tags'))->toBeTrue();
});

it('creates tallpress_comments table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_comments'))->toBeTrue();
});

it('creates tallpress_post_category pivot table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_post_category'))->toBeTrue();
});

it('creates tallpress_post_tag pivot table', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('tallpress_post_tag'))->toBeTrue();
});

it('post can belong to many categories', function () {
    $post = Post::factory()->create(['author_id' => 1]);
    $categories = Category::factory(3)->create();

    $post->categories()->attach($categories);

    expect($post->categories)->toHaveCount(3);
});

it('post can belong to many tags', function () {
    $post = Post::factory()->create(['author_id' => 1]);
    $tags = Tag::factory(3)->create();

    $post->tags()->attach($tags);

    expect($post->tags)->toHaveCount(3);
});

it('post can have many comments', function () {
    $post = Post::factory()->create(['author_id' => 1]);
    Comment::factory(5)->create(['post_id' => $post->id]);

    expect($post->comments)->toHaveCount(5);
});

it('auto generates slug from title', function () {
    $post = Post::factory()->create([
        'title' => 'My Awesome TallPress Post',
        'slug' => null,
        'author_id' => 1,
    ]);

    expect($post->slug)->toBe('my-awesome-tallpress-post');
});

it('sets published_at when status is published', function () {
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => null,
        'author_id' => 1,
    ]);

    expect($post->published_at)->not->toBeNull();
});

it('published scope filters only published posts', function () {
    Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'author_id' => 1]);
    Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'author_id' => 1]);
    Post::factory()->create(['status' => 'draft', 'author_id' => 1]);

    expect(Post::published()->count())->toBe(2);
});

it('search scope filters posts by search term', function () {
    Post::factory()->create([
        'title' => 'Unique Laravel Framework Guide',
        'body' => 'This is about the Laravel PHP framework',
        'author_id' => 1,
    ]);
    Post::factory()->create([
        'title' => 'JavaScript Programming Essentials',
        'body' => 'Learn JavaScript programming fundamentals',
        'author_id' => 1,
    ]);

    // Search should find only posts matching the search term
    expect(Post::search('Laravel')->count())->toBe(1);
    expect(Post::search('JavaScript')->count())->toBe(1);
    expect(Post::search('Nonexistent')->count())->toBe(0);
});

it('post has body html attribute from markdown', function () {
    $post = Post::factory()->create([
        'body' => '## Heading

This is a paragraph.',
        'author_id' => 1,
    ]);

    expect($post->body_html)->toContain('<h2>Heading</h2>');
    expect($post->body_html)->toContain('<p>This is a paragraph.</p>');
});
