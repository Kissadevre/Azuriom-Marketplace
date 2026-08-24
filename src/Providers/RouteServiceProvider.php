<?php

namespace Azuriom\Plugin\Marketplace\Providers;

use Azuriom\Extensions\Plugin\BaseRouteServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public function loadRoutes(): void
    {
        Route::middleware('web')->prefix('marketplace')->name('marketplace.')
            ->group(plugin_path('marketplace/routes/web.php'));

        Route::middleware(['admin-access', 'can:marketplace.admin'])
            ->prefix('admin/marketplace')->name('marketplace.admin.')
            ->group(plugin_path('marketplace/routes/admin.php'));
    }
}
