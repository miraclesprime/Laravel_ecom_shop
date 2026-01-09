<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('products', 'products')
    ->middleware(['auth', 'verified'])
    ->name('products');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// OAuth routes
Route::get('auth/{provider}/redirect', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])
    ->name('auth.social.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
    ->name('auth.social.callback');

require __DIR__.'/auth.php';
