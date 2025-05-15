<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\OverviewDesktop;
use App\Http\Livewire\OverviewMobile;

Route::middleware(['auth'])->group(function () {

    Route::get('/overview-desktop', OverviewDesktop::class)->name('overview-desktop');
    Route::get('/overview-mobile', OverviewMobile::class)->name('overview-mobile');

});