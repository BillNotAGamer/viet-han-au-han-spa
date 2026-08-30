<?php

declare(strict_types=1);

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Localization Routes
|--------------------------------------------------------------------------
*/

// Canonical redirect: /vi -> / (301 Permanent Redirect)
Route::get('/vi', function () {
    return redirect('/', 301);
});

// Vietnamese (Default Canonical – No Prefix)
Route::middleware('set.locale:vi')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('vi.home');
    Route::get('/dich-vu', [ServiceController::class, 'index'])->name('vi.services.index');
    Route::get('/dich-vu/{slug}', [ServiceController::class, 'show'])->name('vi.services.show');
});

// English (Secondary Canonical – /en Prefix)
Route::prefix('en')->middleware('set.locale:en')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('en.home');
    Route::get('/services', [ServiceController::class, 'index'])->name('en.services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('en.services.show');
});
