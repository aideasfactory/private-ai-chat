<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard', [
        'apiKey' => config('openrouter.api_key') ? 'configured' : 'not_configured',
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

// Test route for debugging
Route::get('/api/test-auth', function () {
    return [
        'authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session' => session()->all(),
    ];
})->middleware('auth:sanctum');
