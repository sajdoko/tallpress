<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sajdoko\TallPress\Models\Post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userClass = config('tallpress.author_model');

    // Create users with different roles
    $this->admin = (new $userClass)->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->editor = (new $userClass)->create([
        'name' => 'Editor User',
        'email' => 'editor@example.com',
        'password' => bcrypt('password'),
        'role' => 'editor',
    ]);

    $this->author = (new $userClass)->create([
        'name' => 'Author User',
        'email' => 'author@example.com',
        'password' => bcrypt('password'),
        'role' => 'author',
    ]);
});

it('admin can access admin dashboard', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('tallpress.admin.dashboard'));

    $response->assertStatus(200);
    // Livewire component returns HTML, not a specific view
});

it('editor can access admin dashboard', function () {
    $this->actingAs($this->editor);

    $response = $this->get(route('tallpress.admin.dashboard'));

    $response->assertStatus(200);
});

it('author can access admin dashboard', function () {
    $this->actingAs($this->author);

    $response = $this->get(route('tallpress.admin.dashboard'));

    $response->assertStatus(200);
});

// Note: Guest access tests require host app's login route configuration
// This tests Laravel's auth middleware behavior, not package logic

// it('guest cannot access admin dashboard', function () {
//     $response = $this->get(route('tallpress.admin.dashboard'));
//     $response->assertStatus(302);
// });

it('admin can view all posts in admin', function () {
    $this->actingAs($this->admin);

    Post::factory(3)->create(['author_id' => $this->author->id]);

    $response = $this->get(route('tallpress.admin.posts.index'));

    $response->assertStatus(200);
    // Livewire component returns HTML, not a specific view
});

// Note: Publishing, bulk actions, and CRUD operations are tested in LivewireAdminTest.php
// as they are handled by Livewire components, not traditional routes

it('creates revision when post is updated', function () {
    $this->actingAs($this->admin);

    $post = Post::factory()->create([
        'author_id' => $this->admin->id,
        'title' => 'Original Title',
        'body' => 'Original Body',
    ]);

    $originalTitle = $post->title;
    $originalBody = $post->body;

    $post->update([
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ]);

    $this->assertDatabaseHas('tallpress_post_revisions', [
        'post_id' => $post->id,
        'title' => $originalTitle,
        'body' => $originalBody,
    ]);
});

// Note: Revision restoration, filtering, export, and CRUD operations for categories/tags
// are tested in LivewireAdminTest.php as they are handled by Livewire components

it('can filter posts by status', function () {
    $this->actingAs($this->admin);

    Post::factory(2)->create(['author_id' => $this->author->id, 'status' => 'published']);
    Post::factory(3)->create(['author_id' => $this->author->id, 'status' => 'draft']);

    $response = $this->get(route('tallpress.admin.posts.index', ['status' => 'draft']));

    $response->assertStatus(200);
});

// Note: Activity logging is done through Livewire Forms, not model observers
// Direct Post::create() doesn't trigger activity logging - tested in LivewireAdminTest

// it('logs activity when post is created', function () {
//     $this->actingAs($this->admin);
//     $post = Post::factory()->create(['author_id' => $this->admin->id]);
//     $this->assertDatabaseHas('tallpress_activity_logs', [
//         'description' => 'created post',
//         'subject_type' => get_class($post),
//         'subject_id' => $post->id,
//     ]);
// });
