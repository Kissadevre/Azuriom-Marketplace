<?php

use Azuriom\Plugin\Marketplace\Controllers\Admin\CategoryController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\ReportController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\RestrictionController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\ResourceController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\SettingsController;
use Azuriom\Plugin\Marketplace\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::get('categories/{category}/resources', [CategoryController::class, 'resources'])
    ->name('categories.resources');
Route::resource('categories', CategoryController::class)->except('show');
Route::resource('tags', TagController::class)->except('show');
Route::get('resources/pending', [ResourceController::class, 'pending'])->name('resources.pending');
Route::get('resources/archived', [ResourceController::class, 'archived'])
    ->middleware('can:marketplace.archive')->name('resources.archived');
Route::patch('resources/archived/{resourceUuid}/restore', [ResourceController::class, 'restore'])
    ->middleware('can:marketplace.archive')->name('resources.archived.restore');
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('restrictions', [RestrictionController::class, 'index'])->name('restrictions.index');
Route::post('restrictions', [RestrictionController::class, 'store'])->name('restrictions.store');
Route::patch('restrictions/{restriction}/lift', [RestrictionController::class, 'lift'])->name('restrictions.lift');
Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::post('settings/discord/test', [SettingsController::class, 'testDiscord'])
    ->name('settings.discord.test');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
