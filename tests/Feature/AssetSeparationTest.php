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
        'role' => 'admin',
    ]);
});

it('loads frontend assets on tallpress index page', function () {
    Post::factory(3)->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.index'));

    $response->assertStatus(200);
    $response->assertSee('vendor/tallpress/css/tallpress-frontend.css', false);
    $response->assertSee('vendor/tallpress/js/tallpress-frontend.js', false);
    $response->assertDontSee('vendor/tallpress/css/tallpress-admin.css', false);
    $response->assertDontSee('vendor/tallpress/js/tallpress-admin.js', false);
});

it('loads frontend assets on single post page', function () {
    $post = Post::factory()->published()->create(['author_id' => $this->user->id]);

    $response = $this->get(route('tallpress.posts.show', $post->slug));

    $response->assertStatus(200);
    $response->assertSee('vendor/tallpress/css/tallpress-frontend.css', false);
    $response->assertSee('vendor/tallpress/js/tallpress-frontend.js', false);
    $response->assertDontSee('vendor/tallpress/css/tallpress-admin.css', false);
    $response->assertDontSee('vendor/tallpress/js/tallpress-admin.js', false);
});

it('loads admin assets on admin dashboard', function () {
    $this->actingAs($this->user);

    // Define gate for admin access
    \Illuminate\Support\Facades\Gate::define('access-tallpress-admin', function ($user) {
        return true;
    });

    $response = $this->get(route('tallpress.admin.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('vendor/tallpress/css/tallpress-admin.css', false);
    $response->assertSee('vendor/tallpress/js/tallpress-admin.js', false);
    $response->assertDontSee('vendor/tallpress/css/tallpress-frontend.css', false);
    $response->assertDontSee('vendor/tallpress/js/tallpress-frontend.js', false);
});

it('verifies frontend assets exist', function () {
    expect(file_exists(public_path('vendor/tallpress/css/tallpress-frontend.css'))
        || file_exists(__DIR__.'/../../public/css/tallpress-frontend.css'))
        ->toBeTrue('Frontend CSS file should exist');

    expect(file_exists(public_path('vendor/tallpress/js/tallpress-frontend.js'))
        || file_exists(__DIR__.'/../../public/js/tallpress-frontend.js'))
        ->toBeTrue('Frontend JS file should exist');
});

it('verifies admin assets exist', function () {
    expect(file_exists(public_path('vendor/tallpress/css/tallpress-admin.css'))
        || file_exists(__DIR__.'/../../public/css/tallpress-admin.css'))
        ->toBeTrue('Admin CSS file should exist');

    expect(file_exists(public_path('vendor/tallpress/js/tallpress-admin.js'))
        || file_exists(__DIR__.'/../../public/js/tallpress-admin.js'))
        ->toBeTrue('Admin JS file should exist');
});

it('verifies admin assets are larger than frontend assets', function () {
    $frontendCss = __DIR__.'/../../public/css/tallpress-frontend.css';
    $adminCss = __DIR__.'/../../public/css/tallpress-admin.css';
    $frontendJs = __DIR__.'/../../public/js/tallpress-frontend.js';
    $adminJs = __DIR__.'/../../public/js/tallpress-admin.js';

    if (file_exists($frontendCss) && file_exists($adminCss)) {
        expect(filesize($adminCss))->toBeGreaterThan(filesize($frontendCss),
            'Admin CSS should be larger than frontend CSS (includes Quill styles)');
    }

    if (file_exists($frontendJs) && file_exists($adminJs)) {
        expect(filesize($adminJs))->toBeGreaterThan(filesize($frontendJs),
            'Admin JS should be larger than frontend JS (includes Quill editor)');
    }
});
