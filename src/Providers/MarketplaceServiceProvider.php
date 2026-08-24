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
            'marketplace.archive' => 'marketplace::admin.permissions.archive',
            'marketplace.pause' => 'marketplace::admin.permissions.pause',
            'marketplace.edit' => 'marketplace::admin.permissions.edit',
            'marketplace.delete' => 'marketplace::admin.permissions.delete',
            'marketplace.delete-comments' => 'marketplace::admin.permissions.delete_comments',
            'marketplace.reset-ratings' => 'marketplace::admin.permissions.reset_ratings',
            'marketplace.download-paid' => 'marketplace::admin.permissions.download_paid',
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
                'type' => 'dropdown',
                'icon' => 'bi bi-shop',
                'permission' => 'marketplace.admin',
                'route' => 'marketplace.admin.*',
                'items' => [
                    'marketplace.admin.categories.index' => trans('marketplace::admin.categories.title'),
                    'marketplace.admin.settings.edit' => trans('marketplace::admin.settings.title'),
                ],
            ],
        ];
    }
}
