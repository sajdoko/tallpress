<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sajdoko\TallPress\Models\Post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userClass = config('tallpress.author_model', 'App\\Models\\User');

    $this->user = (new $userClass)->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('increments views count when post is viewed', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    expect($post->views_count)->toBe(0);

    $this->get(route('tallpress.posts.show', $post->slug));

    $post->refresh();
    expect($post->views_count)->toBe(1);
});

it('does not increment views count multiple times in same session', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    // First view
    $this->get(route('tallpress.posts.show', $post->slug));
    $post->refresh();
    expect($post->views_count)->toBe(1);

    // Second view in same session
    $this->get(route('tallpress.posts.show', $post->slug));
    $post->refresh();
    expect($post->views_count)->toBe(1);
});

it('increments views count for different sessions', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    // First session
    $this->get(route('tallpress.posts.show', $post->slug));
    $post->refresh();
    expect($post->views_count)->toBe(1);

    // Clear session to simulate new visitor
    session()->flush();

    // Second session
    $this->get(route('tallpress.posts.show', $post->slug));
    $post->refresh();
    expect($post->views_count)->toBe(2);
});

it('displays views count on post show page', function () {
    $post = Post::factory()->published()->create([
        'author_id' => $this->user->id,
        'meta' => ['views' => 42, 'read_time' => 5],
    ]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('42');
    $response->assertSee('views');
});

it('displays views count on post index page', function () {
    $post = Post::factory()->published()->create([
        'author_id' => $this->user->id,
        'meta' => ['views' => 123, 'read_time' => 5],
    ]);

    $response = $this->get(route('tallpress.posts.index'));

    $response->assertSee('123');
});

it('displays singular view text when count is 1', function () {
    $post = Post::factory()->published()->create([
        'author_id' => $this->user->id,
        'meta' => ['views' => 0, 'read_time' => 5],
    ]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('1 view');
});

it('can manually increment views count', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    expect($post->views_count)->toBe(0);

    $post->incrementViews();
    $post->refresh();

    expect($post->views_count)->toBe(1);

    $post->incrementViews();
    $post->refresh();

    expect($post->views_count)->toBe(2);
});
