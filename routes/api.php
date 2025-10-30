<?php

use Illuminate\Support\Facades\Route;
use Sajdoko\TallPress\Http\Controllers\Api\PostController;

Route::get('/posts', [PostController::class, 'index'])->name('api.tallpress.posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('api.tallpress.posts.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('api.tallpress.posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('api.tallpress.posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('api.tallpress.posts.destroy');
});
