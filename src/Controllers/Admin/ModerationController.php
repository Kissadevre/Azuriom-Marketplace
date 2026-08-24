<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index()
    {
        return view('marketplace::admin.moderation', [
            'resources' => Resource::with(['author', 'category'])
                ->where('status', 'pending')
                ->oldest()
                ->paginate(),
        ]);
    }

    public function approve(Resource $resource)
    {
        abort_unless($resource->status === 'pending', 409);

        $resource->update([
            'status' => 'published',
            'published_at' => now(),
            'moderation_note' => null,
        ]);

        (new AlertNotification(trans('marketplace::messages.notifications.approved', [
            'resource' => $resource->name,
        ])))
            ->level('success')
            ->link(route('marketplace.resources.show', $resource, false))
            ->send($resource->author);

        return back()->with('success', trans('marketplace::admin.moderation.approved'));
    }

    public function reject(Request $request, Resource $resource)
    {
        abort_unless($resource->status === 'pending', 409);

        $data = $request->validate([
            'moderation_note' => ['required', 'string', 'max:2000'],
        ]);

        $resource->update([
            'status' => 'rejected',
            'published_at' => null,
            ...$data,
        ]);

        (new AlertNotification(trans('marketplace::messages.notifications.rejected', [
            'resource' => $resource->name,
            'reason' => $data['moderation_note'],
        ])))
            ->level('danger')
            ->link(route('marketplace.resources.show', $resource, false))
            ->send($resource->author);

        return back()->with('success', trans('marketplace::admin.moderation.rejected'));
    }
}
