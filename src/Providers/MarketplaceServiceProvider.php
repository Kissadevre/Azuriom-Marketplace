<?php

namespace Azuriom\Plugin\Marketplace\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;

class MarketplaceServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();

        Permission::registerPermissions([
            'marketplace.admin' => 'marketplace::admin.permissions.admin',
            'marketplace.moderate' => 'marketplace::admin.permissions.moderate',
            'marketplace.bypass-moderation' => 'marketplace::admin.permissions.bypass_moderation',
        ]);

        Relation::morphMap(['marketplace.resources' => Resource::class]);
    }

    protected function routeDescriptions(): array
    {
        return ['marketplace.index' => trans('marketplace::messages.title')];
    }

    protected function adminNavigation(): array
    {
        return [
            'marketplace' => [
                'name' => trans('marketplace::admin.title'),
                'icon' => 'bi bi-shop',
                'permission' => 'marketplace.admin',
                'route' => 'marketplace.admin.categories.index',
            ],
        ];
    }
}
