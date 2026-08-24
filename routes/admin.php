<?php

use Azuriom\Plugin\Marketplace\Controllers\Admin\CategoryController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\ModerationController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryController::class)->except('show');
Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
Route::patch('moderation/{resource}/approve', [ModerationController::class, 'approve'])->name('moderation.approve');
Route::patch('moderation/{resource}/reject', [ModerationController::class, 'reject'])->name('moderation.reject');
Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
