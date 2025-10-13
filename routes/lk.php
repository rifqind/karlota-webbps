<?php

use App\Http\Controllers\LK\LkController;
use Illuminate\Support\Facades\Route;

Route::prefix('lk')->name('lk.')->group(function () {
    Route::get('/dashboard', [LkController::class, 'dashboard'])
        ->name('dashboard');
});
