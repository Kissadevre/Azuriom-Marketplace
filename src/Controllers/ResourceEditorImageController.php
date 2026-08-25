<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Models\ResourceImage;
use Azuriom\Plugin\Marketplace\Support\ResourceEditorImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ResourceEditorImageController extends Controller
{
    public function store(Request $request, ResourceEditorImageProcessor $processor): JsonResponse
    {
        $maximumSize = min(max((int) setting('marketplace.max_editor_image_size', 5120), 100), 20480);
        $maximumImages = min(max((int) setting('marketplace.max_editor_images', 20), 1), 100);
        $data = $request->validate([
            'draft_token' => ['required', 'uuid'],
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:'.$maximumSize,
                'dimensions:max_width=4096,max_height=4096',
            ],
        ]);

        $this->deleteExpiredDrafts();

        $draftQuery = ResourceImage::query()
            ->whereNull('resource_id')
            ->where('user_id', $request->user()->id);

        if ((clone $draftQuery)->where('draft_token', $data['draft_token'])->count() >= $maximumImages
            || (clone $draftQuery)->count() >= $maximumImages * 3) {
            throw ValidationException::withMessages([
                'image' => trans('marketplace::messages.editor_images.too_many', ['count' => $maximumImages]),
            ]);
        }

        $processed = $processor->process($request->file('image'));
        if (strlen($processed['contents']) > $maximumSize * 1024) {
            throw ValidationException::withMessages([
                'image' => trans('marketplace::messages.editor_images.too_large', ['size' => $maximumSize]),
            ]);
        }

        $uuid = (string) Str::uuid();
        $path = 'marketplace/editor-images/'.$uuid.'.'.$processed['extension'];

        abort_unless(Storage::disk('local')->put($path, $processed['contents']), 500);

        try {
            $image = new ResourceImage([
                'user_id' => $request->user()->id,
                'draft_token' => $data['draft_token'],
                'path' => $path,
                'mime_type' => $processed['mime'],
                'size' => strlen($processed['contents']),
                'width' => $processed['width'],
                'height' => $processed['height'],
            ]);
            $image->uuid = $uuid;
            $image->save();
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return response()->json([
            'location' => route('marketplace.editor-images.show', $image),
        ], 201);
    }

    public function show(Request $request, ResourceImage $resourceImage): StreamedResponse
    {
        if ($resourceImage->resource_id === null) {
            abort_unless($request->user()?->id === $resourceImage->user_id, 404);
        } else {
            $resource = $resourceImage->resource;
            abort_unless($resource !== null && $this->canView($request, $resource), 404);
        }

        abort_unless(Storage::disk('local')->exists($resourceImage->path), 404);

        return Storage::disk('local')->response($resourceImage->path, null, [
            'Content-Type' => $resourceImage->mime_type,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function deleteExpiredDrafts(): void
    {
        ResourceImage::query()
            ->whereNull('resource_id')
            ->where('created_at', '<', now()->subDay())
            ->limit(100)
            ->get()
            ->each->delete();
    }

    private function canView(Request $request, Resource $resource): bool
    {
        if ($resource->status === 'published' && $resource->category->canAccess($request->user())) {
            return true;
        }

        if ($resource->isOwnedBy($request->user())) {
            return true;
        }

        return $request->user() !== null && collect([
            'marketplace.admin',
            'marketplace.moderate',
            'marketplace.archive',
            'marketplace.pause',
            'marketplace.edit',
            'marketplace.delete',
            'marketplace.delete-comments',
            'marketplace.reset-ratings',
        ])->contains(fn (string $permission) => $request->user()->can($permission));
    }
}
