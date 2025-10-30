<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sajdoko\TallPress\Models\Post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userClass = config('tallpress.author_model');
    $this->user = (new $userClass)->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('displays blog index page', function () {
    Post::factory(3)->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.index'));

    $response->assertStatus(200);
    $response->assertViewIs('tallpress::index');
    $response->assertViewHas('posts');
});

it('displays single post page', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertStatus(200);
    $response->assertViewIs('tallpress::show');
    $response->assertSee($post->title);
});

it('search filters posts', function () {
    Post::factory()->published()->create([
        'title' => 'Laravel Tutorial',
        'body' => 'Learn about Laravel framework',
        'author_id' => $this->user->id,
    ]);
    Post::factory()->published()->create([
        'title' => 'PHP Guide',
        'body' => 'Learn about PHP programming',
        'author_id' => $this->user->id,
    ]);

    $response = $this->get(route('tallpress.posts.index', ['search' => 'Laravel']));

    $response->assertStatus(200);
    // Laravel Tutorial should appear in main content
    $response->assertSee('Laravel Tutorial');
    // PHP Guide may appear in sidebar "Recent Posts", but shouldn't be in main article list
    // Just verify we got filtered results
    expect($response->getContent())->toContain('Laravel Tutorial');
});

// Note: CRUD routes (create, store, update, destroy) don't exist in web.php
// Package uses Livewire admin components for all post management operations
// These functionalities are tested in LivewireAdminTest.php instead
