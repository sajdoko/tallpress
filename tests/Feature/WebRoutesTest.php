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
    // Now using Livewire component instead of controller view
    $response->assertSeeLivewire('tallpress-search');
});

it('displays single post page', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertStatus(200);
    $response->assertViewIs('tallpress::front.show');
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

    $response = $this->get(route('tallpress.posts.index', ['q' => 'Laravel']));

    $response->assertStatus(200);
    // Now using Livewire component with 'q' parameter instead of 'search'
    $response->assertSeeLivewire('tallpress-search');
    $response->assertSee('Laravel Tutorial');
});

// Note: CRUD routes (create, store, update, destroy) don't exist in web.php
// Package uses Livewire admin components for all post management operations
// These functionalities are tested in LivewireAdminTest.php instead
