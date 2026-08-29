<?php

namespace Azuriom\Plugin\Marketplace\Support;

use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Models\ResourceImage;
use Illuminate\Validation\ValidationException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Parser\MarkdownParser;

class ResourceEditorImageManager
{
    public function assertWithinLimit(?Resource $resource, User $user, string $draftToken, string $markdown): void
    {
        $uuids = $this->referencedUuids($markdown);
        $query = ResourceImage::query()->whereIn('uuid', $uuids)->where(function ($query) use ($resource, $user, $draftToken) {
            $query->where(function ($query) use ($user, $draftToken) {
                $query->whereNull('resource_id')
                    ->where('user_id', $user->id)
                    ->where('draft_token', $draftToken);
            });

            if ($resource !== null) {
                $query->orWhere('resource_id', $resource->id);
            }
        });

        $maximum = min(max((int) setting('marketplace.max_editor_images', 20), 1), 100);
        if ($query->count() > $maximum) {
            throw ValidationException::withMessages([
                'description' => trans('marketplace::messages.editor_images.too_many', ['count' => $maximum]),
            ]);
        }
    }

    public function synchronize(Resource $resource, User $user, string $draftToken, string $markdown): void
    {
        $this->assertWithinLimit($resource, $user, $draftToken, $markdown);
        $uuids = $this->referencedUuids($markdown);
        $temporary = ResourceImage::query()
            ->whereNull('resource_id')
            ->where('user_id', $user->id)
            ->where('draft_token', $draftToken);

        $ownedUuids = $resource->images()->pluck('uuid')
            ->merge((clone $temporary)->pluck('uuid'))
            ->intersect($uuids)
            ->unique()
            ->values();

        if ($ownedUuids->isNotEmpty()) {
            (clone $temporary)->whereIn('uuid', $ownedUuids)->update([
                'resource_id' => $resource->id,
                'draft_token' => null,
            ]);
        }

        (clone $temporary)->get()->each->delete();

        $unused = $resource->images();
        if ($uuids !== []) {
            $unused->whereNotIn('uuid', $uuids);
        }
        $unused->get()->each->delete();
    }

    /** @return array<int, string> */
    private function referencedUuids(string $markdown): array
    {
        if (trim($markdown) === '') {
            return [];
        }

        $environment = (new Environment)->addExtension(new CommonMarkCoreExtension);
        $document = (new MarkdownParser($environment))->parse($markdown);

        $marker = '00000000-0000-4000-8000-000000000000';
        $routePath = route('marketplace.editor-images.show', ['resourceImage' => $marker], false);
        $prefix = substr($routePath, 0, -strlen($marker));
        $uuids = [];

        foreach ($document->iterator() as $node) {
            if (! $node instanceof Image) {
                continue;
            }

            $path = parse_url($node->getUrl(), PHP_URL_PATH);
            if (! is_string($path) || ! str_starts_with($path, $prefix)) {
                continue;
            }

            $uuid = substr($path, strlen($prefix));
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
                $uuids[] = strtolower($uuid);
            }
        }

        return array_values(array_unique($uuids));
    }
}
