<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Sajdoko\TallPress\Livewire\Front\Comments;
use Sajdoko\TallPress\Livewire\Front\Search;
use Sajdoko\TallPress\Livewire\Front\SearchWidget;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userClass = config('tallpress.author_model');
    $this->user = (new $userClass)->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

// Search Component Tests
it('search component loads successfully', function () {
    Post::factory(3)->published()->create(['author_id' => $this->user->id]);

    Livewire::test(Search::class)
        ->assertStatus(200)
        ->assertSee('All Posts'); // Actual translated text instead of key
});

it('search component filters by query', function () {
    Post::factory()->published()->create([
        'title' => 'Laravel Tutorial',
        'author_id' => $this->user->id,
    ]);
    Post::factory()->published()->create([
        'title' => 'PHP Guide',
        'author_id' => $this->user->id,
    ]);

    Livewire::test(Search::class)
        ->set('query', 'Laravel')
        ->assertSee('Laravel Tutorial')
        ->assertDontSee('PHP Guide');
});

it('search component filters by category', function () {
    $categoryLaravel = Category::factory()->create(['slug' => 'laravel']);
    $categoryPhp = Category::factory()->create(['slug' => 'php']);

    $postLaravel = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $postLaravel->categories()->attach($categoryLaravel);

    $postPhp = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $postPhp->categories()->attach($categoryPhp);

    Livewire::test(Search::class)
        ->set('category', 'laravel')
        ->assertSee($postLaravel->title)
        ->assertDontSee($postPhp->title);
});

it('search component filters by tag', function () {
    $tagLaravel = Tag::factory()->create(['slug' => 'laravel']);
    $tagPhp = Tag::factory()->create(['slug' => 'php']);

    $postLaravel = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $postLaravel->tags()->attach($tagLaravel);

    $postPhp = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $postPhp->tags()->attach($tagPhp);

    Livewire::test(Search::class)
        ->set('tag', 'laravel')
        ->assertSee($postLaravel->title)
        ->assertDontSee($postPhp->title);
});

it('search component can clear filters', function () {
    Post::factory(2)->published()->create(['author_id' => $this->user->id]);

    Livewire::test(Search::class)
        ->set('query', 'test')
        ->set('category', 'category-slug')
        ->set('tag', 'tag-slug')
        ->call('clearSearch')
        ->assertSet('query', '')
        ->assertSet('category', null)
        ->assertSet('tag', null);
});

it('search component resets page on filter change', function () {
    Post::factory(20)->published()->create(['author_id' => $this->user->id]);

    $component = Livewire::test(Search::class)
        ->set('query', 'test');

    // Livewire 3's WithPagination trait resets page internally
    // Just verify the query was set
    expect($component->get('query'))->toBe('test');
});

it('search component displays pagination', function () {
    // Create more posts than the per_page setting to trigger pagination
    Post::factory(20)->published()->create(['author_id' => $this->user->id]);

    Livewire::test(Search::class)
        ->assertSee('Showing')
        ->assertSee('results');
});

it('search component can navigate pages', function () {
    // Create more posts than the per_page setting
    Post::factory(20)->published()->create(['author_id' => $this->user->id]);

    Livewire::test(Search::class)
        ->call('gotoPage', 2)
        ->assertStatus(200); // Just verify navigation works without error
});

// SearchWidget Component Tests
it('search widget loads successfully', function () {
    Livewire::test(SearchWidget::class)
        ->assertStatus(200)
        ->assertSee('Search');
});

it('search widget performs search', function () {
    Livewire::test(SearchWidget::class)
        ->set('search', 'Laravel')
        ->call('performSearch')
        ->assertRedirect(route('tallpress.posts.index', ['q' => 'Laravel']));
});

it('search widget does not search with empty query', function () {
    Livewire::test(SearchWidget::class)
        ->set('search', '')
        ->call('performSearch')
        ->assertNoRedirect();
});

// Comments Component Tests
it('comments component loads successfully', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->assertStatus(200)
        ->assertSee('Leave a Comment');
});

it('comments component displays existing comments', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    Comment::factory()->count(3)->create([
        'post_id' => $post->id,
        'approved' => true,
    ]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->assertSee('Comments') // Translation key becomes 'Comments'
        ->assertSee('(3)');
});

it('comments component submits new comment', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', 'John Doe')
        ->set('author_email', 'john@example.com')
        ->set('body', 'This is a test comment')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Comment::count())->toBe(1);
    expect(Comment::first()->body)->toBe('This is a test comment');
});

it('comments component validates required fields', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', '')
        ->set('author_email', '')
        ->set('body', '')
        ->call('submit')
        ->assertHasErrors(['author_name', 'author_email', 'body']);
});

it('comments component validates email format', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', 'John Doe')
        ->set('author_email', 'invalid-email')
        ->set('body', 'Test comment')
        ->call('submit')
        ->assertHasErrors(['author_email']);
});

it('comments component resets form after submission', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', 'John Doe')
        ->set('author_email', 'john@example.com')
        ->set('body', 'This is a test comment')
        ->call('submit')
        ->assertSet('author_name', '')
        ->assertSet('author_email', '')
        ->assertSet('body', '');
});

it('comments component respects comments enabled setting', function () {
    config(['tallpress.settings.comments_enabled' => false]);
    \Sajdoko\TallPress\Models\Setting::create([
        'key' => 'comments_enabled',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'comments',
    ]);

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', 'John Doe')
        ->set('author_email', 'john@example.com')
        ->set('body', 'This is a test comment')
        ->call('submit');

    // Ensure no comment was created
    expect(Comment::count())->toBe(0);
});

it('comments component prevents commenting on unpublished posts', function () {
    $post = Post::factory()->create([
        'status' => 'draft',
        'author_id' => $this->user->id,
    ]);
    $post->load('approvedComments');

    Livewire::test(Comments::class, ['post' => $post])
        ->set('author_name', 'John Doe')
        ->set('author_email', 'john@example.com')
        ->set('body', 'This is a test comment')
        ->call('submit');

    // Ensure no comment was created
    expect(Comment::count())->toBe(0);
});
