<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Requests\ResourceUpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResourceUpdateController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(ResourceUpdateRequest $request, Resource $resource)
    {
        $data = $request->validated();
        $newFilePath = null;

        if ($resource->delivery_type === 'file') {
            $newFilePath = $request->file('file')->store('marketplace/resources', 'local');
        }

        try {
            $oldFilePath = DB::transaction(function () use ($request, $resource, $data, $newFilePath) {
                $lockedResource = Resource::query()->lockForUpdate()->findOrFail($resource->id);
                abort_unless($lockedResource->delivery_type === $resource->delivery_type, 409);
                $replacedFilePath = $lockedResource->file_path;

                $lockedResource->updates()->create([
                    'user_id' => $request->user()->id,
                    'version' => $data['version'],
                    'description' => $data['description'],
                ]);

                $lockedResource->update([
                    'version' => $data['version'],
                    'file_path' => $lockedResource->delivery_type === 'file' ? $newFilePath : null,
                    'external_url' => $lockedResource->delivery_type === 'external' ? $data['external_url'] : null,
                ]);

                return $replacedFilePath;
            });
        } catch (Throwable $exception) {
            if ($newFilePath !== null) {
                Storage::disk('local')->delete($newFilePath);
            }

            throw $exception;
        }

        if ($newFilePath !== null && $oldFilePath !== null && $oldFilePath !== $newFilePath) {
            Storage::disk('local')->delete($oldFilePath);
        }

        $resource->refresh();

        return to_route('marketplace.resources.show', $resource)
            ->with('success', trans('marketplace::messages.updates.published'));
    }
}
