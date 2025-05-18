<?php

use Illuminate\Support\Facades\Route;
use Autonic\Restuser\Http\Livewire\SelectResource;
use Autonic\Restuser\Http\Livewire\LoginPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Volt;

Route::redirect('/login', '/'); // this redirects /login to /, which is the login page

Route::get('/select-resource', SelectResource::class)->middleware('auth')->name('select-resource');

Route::view('/desktop-only-splash', 'restuser::desktop-only-splash')->middleware('auth')->name('desktop-only-splash');

Route::middleware('web')->group(function () {

    Route::get('/', LoginPage::class)->name('login');

    Route::get('/logout', function () {
        Log::info('Logout - Before: Session: ' . json_encode(session()->all()));
        Log::info('Logout - Before: Auth check: ' . (auth()->check() ? 'true' : 'false'));

        auth()->logout(); // Uses restuser guard
        Session::invalidate();
        Session::regenerateToken();

        Log::info('Logout - After: Session: ' . json_encode(session()->all()));
        Log::info('Logout - After: Auth check: ' . (auth()->check() ? 'true' : 'false'));

        return redirect('/');
    })->name('logout');
});