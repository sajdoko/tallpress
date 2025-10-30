<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sajdoko\TallPress\Database\Seeders\TallPressSeeder;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

uses(RefreshDatabase::class);

it('creates sample users when none exist', function () {
    $userClass = config('tallpress.author_model');

    // Verify no users exist
    expect($userClass::count())->toBe(0);

    // Run seeder with container set
    $seeder = new TallPressSeeder;
    $seeder->setContainer($this->app);
    $seeder->run();

    // Verify users were created
    expect($userClass::count())->toBe(3);
    expect($userClass::where('email', 'admin@example.com')->exists())->toBeTrue();
    expect($userClass::where('email', 'editor@example.com')->exists())->toBeTrue();
    expect($userClass::where('email', 'author@example.com')->exists())->toBeTrue();
});

it('uses existing users when they already exist', function () {
    $userClass = config('tallpress.author_model');

    // Create a user first
    $existingUser = $userClass::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => bcrypt('password'),
    ]);

    expect($userClass::count())->toBe(1);

    // Run seeder with container set
    $seeder = new TallPressSeeder;
    $seeder->setContainer($this->app);
    $seeder->run();

    // Verify no additional sample users were created
    expect($userClass::count())->toBe(1);
    expect($userClass::where('email', 'admin@example.com')->exists())->toBeFalse();
});

it('creates tallpress content when seeder runs', function () {
    $userClass = config('tallpress.author_model');

    // Run seeder with container set
    $seeder = new TallPressSeeder;
    $seeder->setContainer($this->app);
    $seeder->run();

    // Verify tallpress content was created
    expect(Category::count())->toBe(5);
    expect(Tag::count())->toBe(10);
    expect(Post::count())->toBe(20); // 15 published + 5 draft
    expect(Post::where('status', 'published')->count())->toBe(15);
    expect(Post::where('status', 'draft')->count())->toBe(5);
});

it('assigns roles to sample users when role field is enabled', function () {
    config(['tallpress.roles.add_role_field' => true]);

    $userClass = config('tallpress.author_model');

    // Run seeder with container set
    $seeder = new TallPressSeeder;
    $seeder->setContainer($this->app);
    $seeder->run();

    // Verify roles were assigned
    $admin = $userClass::where('email', 'admin@example.com')->first();
    $editor = $userClass::where('email', 'editor@example.com')->first();
    $author = $userClass::where('email', 'author@example.com')->first();

    expect($admin->role)->toBe('admin');
    expect($editor->role)->toBe('editor');
    expect($author->role)->toBe('author');
});
