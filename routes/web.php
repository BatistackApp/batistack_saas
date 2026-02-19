<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/test', function () {
    if (19600 > 0 && (19600 % 20000) > 19500) {
        dd(true);
    }
    dd(false);
});

require __DIR__.'/settings.php';
