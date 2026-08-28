<?php

use Azuriom\Plugin\Marketplace\Controllers\CommentController;
use Azuriom\Plugin\Marketplace\Controllers\HomeController;
use Azuriom\Plugin\Marketplace\Controllers\GiftCodeController;
use Azuriom\Plugin\Marketplace\Controllers\ModerationController;
use Azuriom\Plugin\Marketplace\Controllers\RatingController;
use Azuriom\Plugin\Marketplace\Controllers\ReportController;
use Azuriom\Plugin\Marketplace\Controllers\ResourceController;
use Azuriom\Plugin\Marketplace\Controllers\ResourceEditorImageController;
use Azuriom\Plugin\Marketplace\Controllers\ResourceFollowController;
use Azuriom\Plugin\Marketplace\Controllers\ResourceUpdateController;
use Azuriom\Plugin\Marketplace\Http\Middleware\EnsureMarketplaceActionAllowed;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/category/{category:slug}', [HomeController::class, 'category'])->name('categories.show');
Route::get('/resource/{resource:uuid}', [ResourceController::class, 'show'])->name('resources.show');
Route::get('/resource/{resource:uuid}/banner', [ResourceController::class, 'banner'])->name('resources.banner');
Route::get('/resource/{resource:uuid}/download', [ResourceController::class, 'download'])->name('resources.download');
Route::post('/resource/{resource:uuid}/external', [ResourceController::class, 'continueExternal'])->name('resources.external');
Route::get('/editor-images/{resourceImage:uuid}', [ResourceEditorImageController::class, 'show'])->name('editor-images.show');

Route::middleware('auth')->group(function () {
    Route::get('/my-resources', [HomeController::class, 'mine'])->name('resources.mine');
    Route::get('/purchased-resources', [HomeController::class, 'purchased'])->name('resources.purchased');
    Route::get('/gift-codes', [GiftCodeController::class, 'index'])->name('gift-codes.index');
    Route::post('/gift-codes', [GiftCodeController::class, 'store'])->name('gift-codes.store');
    Route::post('/gift-codes/redeem', [GiftCodeController::class, 'redeem'])->name('gift-codes.redeem');
    Route::get('/submit', [ResourceController::class, 'create'])->middleware(['can:marketplace.publish', EnsureMarketplaceActionAllowed::class.':publish'])->name('resources.create');
    Route::post('/submit', [ResourceController::class, 'store'])->middleware(['can:marketplace.publish', EnsureMarketplaceActionAllowed::class.':publish', 'throttle:marketplace.publish', 'captcha'])->name('resources.store');
    Route::get('/resource/{resource:uuid}/edit', [ResourceController::class, 'edit'])->middleware(EnsureMarketplaceActionAllowed::class.':edit')->name('resources.edit');
    Route::put('/resource/{resource:uuid}', [ResourceController::class, 'update'])->middleware([EnsureMarketplaceActionAllowed::class.':edit', 'throttle:marketplace.edit', 'captcha'])->name('resources.update');
    Route::delete('/resource/{resource:uuid}', [ResourceController::class, 'destroy'])->name('resources.destroy');
    Route::post('/resource/{resource:uuid}/purchase', [ResourceController::class, 'purchase'])->name('resources.purchase');
    Route::post('/resource/{resource:uuid}/follow', [ResourceFollowController::class, 'toggle'])->name('resources.follow');
    Route::post('/resource/{resource:uuid}/comments', [CommentController::class, 'store'])->middleware([EnsureMarketplaceActionAllowed::class.':comment', 'throttle:marketplace.comment', 'captcha'])->name('comments.store');
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike'])->name('comments.likes.toggle');
    Route::post('/resource/{resource:uuid}/report', [ReportController::class, 'resource'])->name('resources.report');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/report', [ReportController::class, 'comment'])->name('comments.report');
    Route::post('/resource/{resource:uuid}/rating', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/resource/{resource:uuid}/updates', [ResourceUpdateController::class, 'store'])->middleware([EnsureMarketplaceActionAllowed::class.':update', 'throttle:marketplace.update', 'captcha'])->name('resources.updates.store');
    Route::post('/editor-images', [ResourceEditorImageController::class, 'store'])
        ->middleware('throttle:30,1')->name('editor-images.store');

    Route::patch('/resource/{resource:uuid}/approve', [ModerationController::class, 'approve'])
        ->middleware('can:marketplace.moderate')->name('resources.approve');
    Route::patch('/resource/{resource:uuid}/reject', [ModerationController::class, 'reject'])
        ->middleware('can:marketplace.moderate')->name('resources.reject');
    Route::patch('/resource/{resource:uuid}/archive', [ModerationController::class, 'archive'])
        ->middleware('can:marketplace.archive')->name('resources.archive');
    Route::patch('/resource/{resource:uuid}/pause', [ModerationController::class, 'pause'])
        ->middleware('can:marketplace.pause')->name('resources.pause');
    Route::patch('/resource/{resource:uuid}/resume', [ModerationController::class, 'resume'])
        ->middleware('can:marketplace.pause')->name('resources.resume');
    Route::delete('/resource/{resource:uuid}/ratings', [ModerationController::class, 'resetRatings'])
        ->middleware('can:marketplace.reset-ratings')->name('resources.ratings.reset');
    Route::delete('/comments/user/{user}', [ModerationController::class, 'destroyUserComments'])
        ->middleware('can:marketplace.delete-comments')->name('comments.user.destroy');
});
