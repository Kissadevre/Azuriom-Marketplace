<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Models\ResourceFollow;
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
        $recipientIds = $resource->purchases()->pluck('user_id')
            ->merge(ResourceFollow::where('resource_id', $resource->id)->pluck('user_id'))
            ->reject(fn ($userId) => (int) $userId === (int) $resource->user_id)
            ->unique()
            ->values();

        User::query()->whereIn('id', $recipientIds)->chunkById(100, function ($users) use ($request, $resource) {
            foreach ($users as $user) {
                (new AlertNotification(trans('marketplace::messages.notifications.updated', [
                    'resource' => $resource->name,
                    'version' => $resource->version,
                ])))
                    ->from($request->user())
                    ->link(route('marketplace.resources.show', [
                        'resource' => $resource,
                        'tab' => 'updates',
                    ], false).'#updates-pane')
                    ->send($user);
            }
        });

        return to_route('marketplace.resources.show', [
            'resource' => $resource,
            'tab' => 'updates',
        ])
            ->with('success', trans('marketplace::messages.updates.published'));
    }
}
