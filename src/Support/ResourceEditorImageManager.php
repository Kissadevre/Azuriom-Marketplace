<?php

namespace Azuriom\Plugin\Marketplace\Support;

use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Models\ResourceImage;
use DOMDocument;
use DOMElement;
use Illuminate\Validation\ValidationException;

class ResourceEditorImageManager
{
    public function assertWithinLimit(?Resource $resource, User $user, string $draftToken, string $html): void
    {
        $uuids = $this->referencedUuids($html);
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

    public function synchronize(Resource $resource, User $user, string $draftToken, string $html): void
    {
        $this->assertWithinLimit($resource, $user, $draftToken, $html);
        $uuids = $this->referencedUuids($html);
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
    private function referencedUuids(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $marker = '00000000-0000-4000-8000-000000000000';
        $routePath = route('marketplace.editor-images.show', ['resourceImage' => $marker], false);
        $prefix = substr($routePath, 0, -strlen($marker));
        $uuids = [];

        foreach ($document->getElementsByTagName('img') as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }

            $path = parse_url(html_entity_decode($image->getAttribute('src')), PHP_URL_PATH);
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
