<?php
use Autonic\Restuser\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {

    /*
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('register', 'pages.auth.register')
        ->name('register');
    */

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

});

Route::middleware('auth')->group(function () {

    Route::view('profile', 'profile')
    ->name('profile');

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
