<?php

namespace Azuriom\Plugin\Marketplace\Support;

class MarketplaceSettings
{
    public const USER_MENU_ENABLED_KEY = 'marketplace.user_menu_enabled';

    public const USER_MENU_ICON_KEY = 'marketplace.user_menu_icon';

    public const DEFAULT_USER_MENU_ICON = 'bi-shop';

    public function showInUserMenu(): bool
    {
        return filter_var(setting(self::USER_MENU_ENABLED_KEY, false), FILTER_VALIDATE_BOOL);
    }

    public function userMenuIcon(): string
    {
        $icon = setting(self::USER_MENU_ICON_KEY, self::DEFAULT_USER_MENU_ICON);

        if (! is_string($icon) || preg_match('/^bi-[a-z0-9]+(?:-[a-z0-9]+)*$/D', $icon) !== 1) {
            return self::DEFAULT_USER_MENU_ICON;
        }

        return $icon;
    }
}
