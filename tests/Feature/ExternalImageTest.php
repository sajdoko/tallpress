<?php

use Sajdoko\TallPress\Models\Post;

it('handles external image URL for featured image', function () {
    $externalImageUrl = 'https://picsum.photos/id/123/1200/630';

    $post = Post::factory()->create([
        'featured_image' => $externalImageUrl,
        'author_id' => 1,
    ]);

    expect($post->featured_image)->toBe($externalImageUrl);
    expect($post->featured_image_url)->toBe($externalImageUrl);
});

it('handles local image path for featured image', function () {
    $localPath = 'images/2024/10/test-image.jpg';

    $post = Post::factory()->create([
        'featured_image' => $localPath,
        'author_id' => 1,
    ]);

    expect($post->featured_image)->toBe($localPath);
    expect($post->featured_image_url)->toContain('storage/'.$localPath);
});

it('distinguishes between external URL and local path', function () {
    $externalUrl = 'https://example.com/image.jpg';
    $localPath = 'images/test.jpg';

    $postWithExternal = Post::factory()->create([
        'featured_image' => $externalUrl,
        'author_id' => 1,
    ]);

    $postWithLocal = Post::factory()->create([
        'featured_image' => $localPath,
        'author_id' => 1,
    ]);

    // External URL should be returned as-is
    expect($postWithExternal->featured_image_url)->toBe($externalUrl);

    // Local path should be converted to storage URL
    expect($postWithLocal->featured_image_url)->toContain('storage/');
    expect($postWithLocal->featured_image_url)->toContain($localPath);
});

it('handles null featured image', function () {
    $post = Post::factory()->create([
        'featured_image' => null,
        'author_id' => 1,
    ]);

    expect($post->featured_image)->toBeNull();
    expect($post->featured_image_url)->toBeNull();
});

it('handles various external image URLs', function () {
    $urls = [
        'https://picsum.photos/id/1/200/300.jpg',
        'https://via.placeholder.com/600/92c952',
        'https://images.unsplash.com/photo-123?w=600',
        'http://example.com/path/to/image.png',
    ];

    foreach ($urls as $url) {
        $post = Post::factory()->create([
            'featured_image' => $url,
            'author_id' => 1,
        ]);

        expect($post->featured_image_url)->toBe($url);
    }
});

// Note: PostForm validation tests are covered in LivewireAdminTest.php
// Direct Form instantiation requires a Livewire Component context

// it('validates external URL format in PostForm', function () {
//     $form = new \Sajdoko\TallPress\Livewire\Forms\PostForm;
//     $form->title = 'Test Post';
//     $form->body = 'Test content';
//     $form->status = 'draft';
//     $form->featured_image_external_url = 'https://example.com/image.jpg';
//     $form->validate();
//     expect($form->featured_image_external_url)->toBe('https://example.com/image.jpg');
// });

// it('rejects invalid external URL format in PostForm', function () {
//     $form = new \Sajdoko\TallPress\Livewire\Forms\PostForm;
//     $form->title = 'Test Post';
//     $form->body = 'Test content';
//     $form->status = 'draft';
//     $form->featured_image_external_url = 'not-a-valid-url';
//     expect(fn () => $form->validate())->toThrow(\Illuminate\Validation\ValidationException::class);
// });

// it('rejects external URL without image extension in PostForm', function () {
//     $form = new \Sajdoko\TallPress\Livewire\Forms\PostForm;
//     $form->title = 'Test Post';
//     $form->body = 'Test content';
//     $form->status = 'draft';
//     $form->featured_image_external_url = 'https://example.com/not-an-image';
//     expect(fn () => $form->validate())->toThrow(\Illuminate\Validation\ValidationException::class);
// });
