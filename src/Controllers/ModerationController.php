<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Support\DiscordWebhookNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    public function approve(Resource $resource, DiscordWebhookNotifier $discordNotifier)
    {
        [$resource, $shouldNotifyDiscord] = DB::transaction(function () use ($resource) {
            $lockedResource = Resource::query()->lockForUpdate()->findOrFail($resource->id);
            abort_unless($lockedResource->status === 'pending', 409);
            $shouldNotifyDiscord = $lockedResource->published_at === null;

            $lockedResource->update([
                'status' => 'published',
                'published_at' => $lockedResource->published_at ?? now(),
                'moderation_note' => null,
            ]);

            return [$lockedResource->fresh(), $shouldNotifyDiscord];
        });

        (new AlertNotification(trans('marketplace::messages.notifications.approved', [
            'resource' => $resource->name,
        ])))
            ->level('success')
            ->link(route('marketplace.resources.show', $resource, false))
            ->send($resource->author);

        if ($shouldNotifyDiscord) {
            $discordNotifier->notifyPublished($resource);
        }

        return back()->with('success', trans('marketplace::messages.moderation.approved'));
    }

    public function reject(Request $request, Resource $resource)
    {
        abort_unless($resource->status === 'pending', 409);

        $data = $request->validate([
            'moderation_note' => ['required', 'string', 'max:2000'],
        ]);

        $resource->update([
            'status' => 'rejected',
            ...$data,
        ]);

        (new AlertNotification(trans('marketplace::messages.notifications.rejected', [
            'resource' => $resource->name,
            'reason' => $data['moderation_note'],
        ])))
            ->level('danger')
            ->link(route('marketplace.resources.show', $resource, false))
            ->send($resource->author);

        return back()->with('success', trans('marketplace::messages.moderation.rejected'));
    }

    public function archive(Resource $resource)
    {
        $resource->update(['archived_at' => now()]);

        return to_route('marketplace.index')
            ->with('success', trans('marketplace::messages.moderation.archived'));
    }

    public function pause(Resource $resource)
    {
        abort_if($resource->isPaused(), 409);
        $resource->update(['paused_at' => now()]);

        return back()->with('success', trans('marketplace::messages.moderation.paused'));
    }

    public function resume(Resource $resource)
    {
        abort_unless($resource->isPaused(), 409);
        $resource->update(['paused_at' => null]);

        return back()->with('success', trans('marketplace::messages.moderation.resumed'));
    }

    public function resetRatings(Resource $resource)
    {
        $resource->ratings()->delete();

        return back()->with('success', trans('marketplace::messages.moderation.ratings_reset'));
    }

    public function destroyUserComments(User $user)
    {
        $count = Comment::where('user_id', $user->id)->delete();

        return back()->with('success', trans('marketplace::messages.moderation.user_comments_deleted', [
            'count' => $count,
            'user' => $user->name,
        ]));
    }
}
