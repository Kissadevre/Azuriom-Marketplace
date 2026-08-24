<?php

use Azuriom\Plugin\Marketplace\Controllers\CommentController;
use Azuriom\Plugin\Marketplace\Controllers\HomeController;
use Azuriom\Plugin\Marketplace\Controllers\RatingController;
use Azuriom\Plugin\Marketplace\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/category/{category:slug}', [HomeController::class, 'category'])->name('categories.show');
Route::get('/resource/{resource}', [ResourceController::class, 'show'])->name('resources.show');

Route::middleware('auth')->group(function () {
    Route::get('/submit', [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/submit', [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/resource/{resource}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::put('/resource/{resource}', [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/resource/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
    Route::post('/resource/{resource}/purchase', [ResourceController::class, 'purchase'])->name('resources.purchase');
    Route::get('/resource/{resource}/download', [ResourceController::class, 'download'])->name('resources.download');
    Route::post('/resource/{resource}/external', [ResourceController::class, 'continueExternal'])->name('resources.external');
    Route::post('/resource/{resource}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/resource/{resource}/rating', [RatingController::class, 'store'])->name('ratings.store');
});
