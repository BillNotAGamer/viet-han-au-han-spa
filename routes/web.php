<?php

declare(strict_types=1);

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

// Vietnamese (Default Canonical — No Prefix)
Route::middleware('set.locale:vi')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('vi.home');
});

// English (Secondary Canonical — /en Prefix)
Route::prefix('en')->middleware('set.locale:en')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('en.home');
});
