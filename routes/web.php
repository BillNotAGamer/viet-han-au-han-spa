<?php

declare(strict_types=1);

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\StaticPageController;
use App\Http\Controllers\Public\TrainingController;
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
    Route::get('/gioi-thieu', [StaticPageController::class, 'about'])->name('vi.about');
    Route::get('/lien-he', [StaticPageController::class, 'contact'])->name('vi.contact');
    Route::get('/dich-vu', [ServiceController::class, 'index'])->name('vi.services.index');
    Route::get('/dich-vu/{slug}', [ServiceController::class, 'show'])->name('vi.services.show');
    Route::get('/dao-tao', [TrainingController::class, 'index'])->name('vi.training.index');
    Route::get('/dao-tao/{slug}', [TrainingController::class, 'show'])->name('vi.training.show');
    Route::get('/blog', [BlogController::class, 'index'])->name('vi.blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('vi.blog.show');
});

// English (Secondary Canonical – /en Prefix)
Route::prefix('en')->middleware('set.locale:en')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('en.home');
    Route::get('/about', [StaticPageController::class, 'about'])->name('en.about');
    Route::get('/contact', [StaticPageController::class, 'contact'])->name('en.contact');
    Route::get('/services', [ServiceController::class, 'index'])->name('en.services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('en.services.show');
    Route::get('/training', [TrainingController::class, 'index'])->name('en.training.index');
    Route::get('/training/{slug}', [TrainingController::class, 'show'])->name('en.training.show');
    Route::get('/blog', [BlogController::class, 'index'])->name('en.blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('en.blog.show');
});
