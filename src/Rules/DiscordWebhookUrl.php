<?php

namespace Azuriom\Plugin\Marketplace\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DiscordWebhookUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($value)) {
            $fail('marketplace::admin.settings.discord_webhook_url_invalid')->translate();
        }
    }

    public static function isValid(mixed $value): bool
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($value);

        if (! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || ! in_array(strtolower((string) ($parts['host'] ?? '')), ['discord.com', 'discordapp.com'], true)
            || array_intersect_key($parts, array_flip(['port', 'user', 'pass', 'query', 'fragment'])) !== []) {
            return false;
        }

        return preg_match(
            '#^/api(?:/v\d+)?/webhooks/\d+/[A-Za-z0-9._-]+/?$#D',
            (string) ($parts['path'] ?? '')
        ) === 1;
    }
}
