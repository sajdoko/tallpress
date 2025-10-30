<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Sajdoko\TallPress\Livewire\Admin\Dashboard;
use Sajdoko\TallPress\Livewire\Admin\Posts\CreateEdit;
use Sajdoko\TallPress\Livewire\Admin\Posts\Index as PostsIndex;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

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

it('dashboard component loads successfully', function () {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Dashboard');
});

it('posts index component loads successfully', function () {
    Livewire::actingAs($this->admin)
        ->test(PostsIndex::class)
        ->assertStatus(200);
});

it('posts index component filters by status', function () {
    $post = Post::factory()->create([
        'status' => 'published',
        'author_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(PostsIndex::class)
        ->set('status', 'published')
        ->assertSee($post->title);
});

it('posts index component searches posts', function () {
    $post = Post::factory()->create([
        'title' => 'Unique Search Term Post',
        'author_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(PostsIndex::class)
        ->set('search', 'Unique Search Term')
        ->assertSee($post->title);
});

it('admin can delete post via livewire', function () {
    $post = Post::factory()->create([
        'author_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(PostsIndex::class)
        ->call('deletePost', $post->id)
        ->assertDispatched('post-deleted');

    expect(Post::withTrashed()->find($post->id)->trashed())->toBeTrue();
});

it('admin can bulk publish posts via livewire', function () {
    $posts = Post::factory()->count(3)->create([
        'status' => 'draft',
        'author_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(PostsIndex::class)
        ->set('selectedPosts', $posts->pluck('id')->toArray())
        ->call('bulkPublish');

    foreach ($posts as $post) {
        expect($post->fresh()->status)->toBe('published');
    }
});

it('create edit component loads for new post', function () {
    Livewire::actingAs($this->admin)
        ->test(CreateEdit::class)
        ->assertStatus(200)
        ->assertSet('isEditMode', false);
});

it('create edit component loads for existing post', function () {
    $post = Post::factory()->create([
        'author_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(CreateEdit::class, ['post' => $post])
        ->assertStatus(200)
        ->assertSet('isEditMode', true)
        ->assertSet('form.title', $post->title);
});

it('admin can create post via livewire', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(CreateEdit::class)
        ->set('form.title', 'New Livewire Post')
        ->set('form.body', 'This is the body of the new post')
        ->set('form.status', 'draft')
        ->set('form.selectedCategories', [$category->id])
        ->set('form.selectedTags', [$tag->id])
        ->call('save');

    expect(Post::where('title', 'New Livewire Post')->exists())->toBeTrue();

    $post = Post::where('title', 'New Livewire Post')->first();
    expect($post->categories)->toHaveCount(1);
    expect($post->tags)->toHaveCount(1);
});

it('admin can update post via livewire', function () {
    $post = Post::factory()->create([
        'author_id' => $this->admin->id,
        'title' => 'Original Title',
    ]);

    Livewire::actingAs($this->admin)
        ->test(CreateEdit::class, ['post' => $post])
        ->set('form.title', 'Updated Title')
        ->call('save');

    expect($post->fresh()->title)->toBe('Updated Title');
});

it('author cannot publish post without permission', function () {
    $post = Post::factory()->create([
        'author_id' => $this->author->id,
        'status' => 'draft',
    ]);

    Livewire::actingAs($this->author)
        ->test(CreateEdit::class, ['post' => $post])
        ->set('form.status', 'published')
        ->call('save')
        ->assertForbidden();
});
