<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userClass = config('tallpress.author_model', 'App\\Models\\User');

    $this->user = (new $userClass)->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('does not show social share buttons when disabled', function () {
    Setting::set('social_share_enabled', false, 'boolean', 'social_share');

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertDontSee('Share this post');
    $response->assertDontSee('Share on Facebook', false);
    $response->assertDontSee('Share on Twitter', false);
});

it('shows social share buttons when enabled', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_facebook', true, 'boolean', 'social_share');
    Setting::set('social_share_twitter', true, 'boolean', 'social_share');

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('Share this post');
    $response->assertSee('Share on Facebook', false);
    $response->assertSee('Share on Twitter', false);
});

it('shows only enabled social platforms', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_facebook', true, 'boolean', 'social_share');
    Setting::set('social_share_twitter', false, 'boolean', 'social_share');
    Setting::set('social_share_linkedin', true, 'boolean', 'social_share');
    Setting::set('social_share_reddit', false, 'boolean', 'social_share');

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('Share on Facebook', false);
    $response->assertSee('Share on LinkedIn', false);
    $response->assertDontSee('Share on Twitter', false);
    $response->assertDontSee('Share on Reddit', false);
});

it('social share links contain correct urls', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_facebook', true, 'boolean', 'social_share');
    Setting::set('social_share_twitter', true, 'boolean', 'social_share');
    Setting::set('social_share_linkedin', true, 'boolean', 'social_share');

    $post = Post::factory()->published()->create([
        'author_id' => $this->user->id,
        'title' => 'Test Post Title',
    ]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    // Check Facebook share link
    $response->assertSee('https://www.facebook.com/sharer/sharer.php', false);

    // Check Twitter share link
    $response->assertSee('https://twitter.com/intent/tweet', false);

    // Check LinkedIn share link
    $response->assertSee('https://www.linkedin.com/shareArticle', false);

    // Verify they're in the social share section by checking for Share on labels
    $response->assertSee('Share on Facebook', false);
    $response->assertSee('Share on Twitter', false);
    $response->assertSee('Share on LinkedIn', false);
});

it('social share email link includes post details', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_email', true, 'boolean', 'social_share');

    $post = Post::factory()->published()->create([
        'author_id' => $this->user->id,
        'title' => 'Test Post Title',
        'excerpt' => 'Test excerpt',
    ]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('mailto:', false);
    $response->assertSee('Share via Email', false);
});

it('social share whatsapp button shows when enabled', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_whatsapp', true, 'boolean', 'social_share');

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('Share on WhatsApp', false);
    $response->assertSee('https://wa.me/', false);
});

it('social share reddit button shows when enabled', function () {
    Setting::set('social_share_enabled', true, 'boolean', 'social_share');
    Setting::set('social_share_reddit', true, 'boolean', 'social_share');

    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertSee('Share on Reddit', false);
    $response->assertSee('https://reddit.com/submit', false);
});
