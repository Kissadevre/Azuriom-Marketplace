<?php

namespace Azuriom\Plugin\Marketplace\Support;

use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Rules\DiscordWebhookUrl;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordWebhookNotifier
{
    public function __construct(private readonly MarketplaceSettings $settings) {}

    public function notifyPublished(Resource $resource): bool
    {
        if (! $this->settings->discordWebhookEnabled() || $resource->status !== 'published') {
            return false;
        }

        $resource->loadMissing(['author', 'category']);

        return $this->send([
            'embeds' => [[
                'title' => trans('marketplace::admin.discord_webhook.published_title'),
                'url' => route('marketplace.resources.show', $resource),
                'description' => $resource->summary,
                'color' => 0x57F287,
                'fields' => $this->resourceFields($resource),
                'timestamp' => ($resource->published_at ?? now())->toIso8601String(),
                'footer' => ['text' => config('app.name')],
            ]],
            'allowed_mentions' => ['parse' => []],
        ], 'resource_published', $resource);
    }

    public function notifyUpdated(Resource $resource): bool
    {
        if (! $this->settings->discordWebhookEnabled() || $resource->status !== 'published') {
            return false;
        }

        $resource->loadMissing(['author', 'category']);

        return $this->send([
            'embeds' => [[
                'title' => trans('marketplace::admin.discord_webhook.updated_title'),
                'url' => route('marketplace.resources.show', [
                    'resource' => $resource,
                    'tab' => 'updates',
                ]).'#updates-pane',
                'description' => trans('marketplace::admin.discord_webhook.updated_description', [
                    'resource' => $resource->name,
                    'version' => $resource->version,
                ]),
                'color' => 0x5865F2,
                'fields' => $this->resourceFields($resource),
                'timestamp' => now()->toIso8601String(),
                'footer' => ['text' => config('app.name')],
            ]],
            'allowed_mentions' => ['parse' => []],
        ], 'resource_updated', $resource);
    }

    public function sendTest(): bool
    {
        return $this->send([
            'embeds' => [[
                'title' => trans('marketplace::admin.discord_webhook.test_title'),
                'description' => trans('marketplace::admin.discord_webhook.test_description'),
                'color' => 0x5865F2,
                'timestamp' => now()->toIso8601String(),
                'footer' => ['text' => config('app.name')],
            ]],
            'allowed_mentions' => ['parse' => []],
        ], 'test');
    }

    /**
     * @return array<int, array{name: string, value: string, inline: bool}>
     */
    private function resourceFields(Resource $resource): array
    {
        return [
            [
                'name' => trans('marketplace::admin.discord_webhook.resource'),
                'value' => $resource->name,
                'inline' => true,
            ],
            [
                'name' => trans('marketplace::admin.discord_webhook.version'),
                'value' => $resource->version ?: '—',
                'inline' => true,
            ],
            [
                'name' => trans('marketplace::admin.discord_webhook.author'),
                'value' => $resource->author?->name ?: '—',
                'inline' => true,
            ],
            [
                'name' => trans('marketplace::admin.discord_webhook.category'),
                'value' => $resource->category?->name ?: '—',
                'inline' => true,
            ],
        ];
    }

    private function send(array $payload, string $event, ?Resource $resource = null): bool
    {
        $url = $this->settings->discordWebhookUrl();

        if (! DiscordWebhookUrl::isValid($url)) {
            Log::warning('Marketplace Discord webhook was not sent because its URL is missing or invalid.', [
                'event' => $event,
                'resource_id' => $resource?->id,
            ]);

            return false;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            $this->logFailure($event, $resource, $response);
        } catch (Throwable $exception) {
            Log::warning('Marketplace Discord webhook request failed.', [
                'event' => $event,
                'resource_id' => $resource?->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        return false;
    }

    private function logFailure(string $event, ?Resource $resource, Response $response): void
    {
        Log::warning('Marketplace Discord webhook returned an unsuccessful response.', [
            'event' => $event,
            'resource_id' => $resource?->id,
            'status' => $response->status(),
        ]);
    }
}
