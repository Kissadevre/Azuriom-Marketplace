<?php

use Azuriom\Plugin\Marketplace\Controllers\Admin\CategoryController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryController::class)->except('show');
Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
