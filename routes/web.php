<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Login route
Volt::route('/', 'pages.auth.login')->name('login');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Volt::route('/dashboard', 'pages.dashboard')->name('dashboard');
    Volt::route('/program', 'pages.program')->name('program');
});

// Logout - Accept both GET and POST
Route::match(['get', 'post'], '/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');
