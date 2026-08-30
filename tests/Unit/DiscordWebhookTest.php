<?php

namespace Tests\Unit;

use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Rules\DiscordWebhookUrl;
use Azuriom\Plugin\Marketplace\Support\DiscordWebhookNotifier;
use Azuriom\Plugin\Marketplace\Support\MarketplaceSettings;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class DiscordWebhookTest extends TestCase
{
    private const WEBHOOK_URL = 'https://discord.com/api/webhooks/123456789/test_token-value';

    public function test_only_official_https_discord_webhook_urls_are_accepted(): void
    {
        $this->assertTrue(DiscordWebhookUrl::isValid(self::WEBHOOK_URL));
        $this->assertTrue(DiscordWebhookUrl::isValid(
            'https://discord.com/api/v10/webhooks/123456789/test.token_value'
        ));

        $this->assertFalse(DiscordWebhookUrl::isValid('http://discord.com/api/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookUrl::isValid('https://discord.com.evil.test/api/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookUrl::isValid('https://discord.com/api/webhooks/1/token?wait=true'));
        $this->assertFalse(DiscordWebhookUrl::isValid('https://user@discord.com/api/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookUrl::isValid('not-a-url'));
    }

    public function test_published_resource_notification_uses_a_safe_discord_payload(): void
    {
        Http::fake([self::WEBHOOK_URL => Http::response('', 204)]);

        $notifier = new DiscordWebhookNotifier($this->settings(enabled: true));

        $this->assertTrue($notifier->notifyPublished($this->resource()));

        Http::assertSent(function ($request) {
            return $request->url() === self::WEBHOOK_URL
                && $request['allowed_mentions'] === ['parse' => []]
                && $request['embeds'][0]['title'] === trans('marketplace::admin.discord_webhook.published_title')
                && $request['embeds'][0]['fields'][0]['value'] === 'Example Resource';
        });
    }

    public function test_network_failures_are_contained_and_never_propagated(): void
    {
        Http::fake(fn () => throw new RuntimeException('Discord is unavailable.'));

        $notifier = new DiscordWebhookNotifier($this->settings(enabled: true));

        $this->assertFalse($notifier->notifyUpdated($this->resource()));
    }

    public function test_unsuccessful_discord_responses_are_contained(): void
    {
        Http::fake([self::WEBHOOK_URL => Http::response(['message' => 'Unavailable'], 503)]);

        $notifier = new DiscordWebhookNotifier($this->settings(enabled: true));

        $this->assertFalse($notifier->notifyPublished($this->resource()));
    }

    public function test_disabled_notifications_do_not_make_http_requests(): void
    {
        Http::fake();

        $notifier = new DiscordWebhookNotifier($this->settings(enabled: false));

        $this->assertFalse($notifier->notifyPublished($this->resource()));
        Http::assertNothingSent();
    }

    private function settings(bool $enabled): MarketplaceSettings
    {
        $settings = $this->createMock(MarketplaceSettings::class);
        $settings->method('discordWebhookEnabled')->willReturn($enabled);
        $settings->method('discordWebhookUrl')->willReturn(self::WEBHOOK_URL);

        return $settings;
    }

    private function resource(): Resource
    {
        $resource = new Resource();
        $resource->forceFill([
            'id' => 42,
            'uuid' => '1c5450d3-22ef-4b98-a342-e8d9110319cf',
            'name' => 'Example Resource',
            'version' => '1.0.0',
            'summary' => 'A resource used to test Discord notifications.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $author = new User();
        $author->forceFill(['id' => 7, 'name' => 'Author']);
        $category = new Category();
        $category->forceFill(['id' => 3, 'name' => 'Plugins']);

        return $resource
            ->setRelation('author', $author)
            ->setRelation('category', $category);
    }
}
