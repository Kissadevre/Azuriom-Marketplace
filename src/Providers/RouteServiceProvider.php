<?php

namespace Azuriom\Plugin\Marketplace\Providers;

use Azuriom\Extensions\Plugin\BaseRouteServiceProvider;
use Azuriom\Plugin\Marketplace\Middleware\LogMarketplaceRequest;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public function loadRoutes(): void
    {
        Route::middleware(['web', LogMarketplaceRequest::class])->prefix('marketplace')->name('marketplace.')
            ->group(plugin_path('marketplace/routes/web.php'));

        Route::middleware(['admin-access', LogMarketplaceRequest::class, 'can:marketplace.admin'])
            ->prefix('admin/marketplace')->name('marketplace.admin.')
            ->group(plugin_path('marketplace/routes/admin.php'));
    }
}
