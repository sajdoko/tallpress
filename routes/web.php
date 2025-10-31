<?php

use Illuminate\Support\Facades\Route;
use Sajdoko\TallPress\Http\Controllers\PostController;
use Sajdoko\TallPress\Livewire\Front\Search;

// Main posts listing - using Livewire Search component
Route::get('/results', Search::class)->name('tallpress.posts.results');

// Blog home page
Route::get('/', [PostController::class, 'index'])->name('tallpress.posts.index');

// Individual post view - still uses controller but with Livewire Comments component
Route::get('/{post:slug}', [PostController::class, 'show'])->name('tallpress.posts.show');
