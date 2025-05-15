<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Autonic\Restuser\Http\Controllers\ApiController;

Route::prefix('api')->group(function () {

    Route::post('/create-default-settings', [ApiController::class, 'createDefaultSettings'])->name('create-default-settings');
    Route::post('/check-default-settings', [ApiController::class, 'checkDefaultSettings'])->name('check-default-settings');
    
});