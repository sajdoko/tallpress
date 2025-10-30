<?php

use Illuminate\Support\Facades\Route;
use Sajdoko\TallPress\Http\Controllers\MediaController;
use Sajdoko\TallPress\Livewire\Admin\Categories\Index as CategoriesIndex;
use Sajdoko\TallPress\Livewire\Admin\Comments\Index as CommentsIndex;
use Sajdoko\TallPress\Livewire\Admin\Dashboard;
use Sajdoko\TallPress\Livewire\Admin\Media\Manager as MediaManager;
use Sajdoko\TallPress\Livewire\Admin\Posts\CreateEdit as PostsCreateEdit;
use Sajdoko\TallPress\Livewire\Admin\Posts\Index as PostsIndex;
use Sajdoko\TallPress\Livewire\Admin\Posts\Revisions as PostsRevisions;
use Sajdoko\TallPress\Livewire\Admin\Settings\Index as SettingsIndex;
use Sajdoko\TallPress\Livewire\Admin\Tags\Index as TagsIndex;
use Sajdoko\TallPress\Livewire\Admin\Users\Index as UsersIndex;

Route::middleware(['auth', 'tallpress.role:admin,editor,author'])->group(function () {
    // Dashboard
    Route::get('/', Dashboard::class)->name('tallpress.admin.dashboard');

    // Posts management
    Route::prefix('posts')->name('tallpress.admin.posts.')->group(function () {
        Route::get('/create', PostsCreateEdit::class)->name('create');
        Route::get('/{post}/edit', PostsCreateEdit::class)->name('edit');
        Route::get('/{post}/revisions', PostsRevisions::class)->name('revisions');
        Route::get('/', PostsIndex::class)->name('index');
    });

    // Categories management
    Route::get('/categories', CategoriesIndex::class)->name('tallpress.admin.categories.index');

    // Tags management
    Route::get('/tags', TagsIndex::class)->name('tallpress.admin.tags.index');

    // Comments moderation
    Route::get('/comments', CommentsIndex::class)->name('tallpress.admin.comments.index');

    // Media management
    Route::get('/media', MediaManager::class)->name('tallpress.admin.media.index');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('tallpress.admin.media.upload');

    // Users management (admin only)
    Route::get('/users', UsersIndex::class)->name('tallpress.admin.users.index')->middleware('tallpress.role:admin');

    // Settings
    Route::get('/settings', SettingsIndex::class)->name('tallpress.admin.settings.index');
});
