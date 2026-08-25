<?php

namespace Azuriom\Plugin\Marketplace\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Marketplace\Commands\CleanupEditorImages;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class MarketplaceServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();
        $this->registerMarketplaceRateLimiters();

        $this->commands(CleanupEditorImages::class);
        if (method_exists($this, 'registerSchedule')) {
            $this->registerSchedule();
        }

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

        Relation::morphMap([
            'marketplace.resources' => Resource::class,
            'marketplace.comments' => Comment::class,
        ]);
    }

    protected function routeDescriptions(): array
    {
        return ['marketplace.index' => trans('marketplace::messages.title')];
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('marketplace:cleanup-editor-images')->daily();
    }

    protected function registerMarketplaceRateLimiters(): void
    {
        $limits = [
            'publish' => ['setting' => 'marketplace.rate_limit_publish', 'default' => 300],
            'edit' => ['setting' => 'marketplace.rate_limit_edit', 'default' => 60],
            'update' => ['setting' => 'marketplace.rate_limit_update', 'default' => 300],
            'comment' => ['setting' => 'marketplace.rate_limit_comment', 'default' => 15],
        ];

        foreach ($limits as $name => $configuration) {
            RateLimiter::for('marketplace.'.$name, function (Request $request) use ($configuration) {
                $seconds = max(0, min(86400, (int) setting(
                    $configuration['setting'],
                    $configuration['default']
                )));

                if ($seconds === 0) {
                    return Limit::none();
                }

                return Limit::perSecond(1, $seconds)
                    ->by($request->user()->getAuthIdentifier().':'.$seconds)
                    ->response(function (Request $request, array $headers) use ($seconds) {
                        return back()
                            ->with('error', trans('marketplace::messages.rate_limit', [
                                'seconds' => $headers['Retry-After'] ?? $seconds,
                            ]))
                            ->withHeaders($headers);
                    });
            });
        }
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
                    'marketplace.admin.tags.index' => trans('marketplace::admin.tags.title'),
                    'marketplace.admin.resources.pending' => trans('marketplace::admin.pending_resources.title'),
                    'marketplace.admin.resources.archived' => [
                        'name' => trans('marketplace::admin.archived_resources.title'),
                        'permission' => 'marketplace.archive',
                    ],
                    'marketplace.admin.reports.index' => trans('marketplace::admin.reports.title'),
                    'marketplace.admin.restrictions.index' => trans('marketplace::admin.restrictions.title'),
                    'marketplace.admin.settings.edit' => trans('marketplace::admin.settings.title'),
                ],
            ],
        ];
    }
}
