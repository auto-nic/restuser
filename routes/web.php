<?php

use Illuminate\Support\Facades\Route;
use Autonic\Restuser\Http\Livewire\SelectResource;
use Autonic\Restuser\Http\Livewire\LoginPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Volt;


Route::redirect('/login', '/'); // this redirects /login to /, which is the login page
Route::get('/', LoginPage::class)->name('login');

Route::get('/select-resource', SelectResource::class)->middleware('auth')->name('select-resource');

Route::view('/desktop-only-splash', 'restuser::desktop-only-splash')->middleware('auth')->name('desktop-only-splash');

Route::get('/logout', function () {
    Auth::guard('web')->logout();
    Session::invalidate();
    Session::regenerateToken();
    return redirect('/');
})->name('logout');