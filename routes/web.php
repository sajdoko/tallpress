<?php

use Illuminate\Support\Facades\Route;
use Sajdoko\TallPress\Http\Controllers\CommentController;
use Sajdoko\TallPress\Http\Controllers\PostController;

Route::get('/', [PostController::class, 'index'])->name('tallpress.posts.index');
Route::get('/{post:slug}', [PostController::class, 'show'])->name('tallpress.posts.show');

// Comment routes
Route::post('/{post:slug}/comments', [CommentController::class, 'store'])->name('tallpress.comments.store');
