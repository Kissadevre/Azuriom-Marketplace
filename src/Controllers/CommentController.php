<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function store(Request $request, Resource $resource)
    {
        abort_if(
            setting('marketplace.pause_comments', false),
            403,
            trans('marketplace::messages.comments_paused')
        );

        abort_unless(
            $resource->status === 'published'
            && ! $resource->isPaused()
            && $resource->category->canAccess($request->user())
            && $resource->isUnlockedBy($request->user()),
            403
        );

        $request->merge([
            'content' => Str::squish(strip_tags((string) $request->input('content', ''))),
        ]);

        $data = $request->validate(
            ['content' => ['required', 'string', 'max:150', 'regex:/^[\pL\pN ]+$/u']],
            ['content.regex' => trans('marketplace::messages.validation.alpha_numeric_spaces')],
            ['content' => trans('marketplace::messages.comment')]
        );
        $resource->comments()->create($data + ['user_id' => $request->user()->id]);

        if (! $request->user()->is($resource->author)) {
            (new AlertNotification(trans('marketplace::messages.notifications.comment', [
                'user' => $request->user()->name,
                'resource' => $resource->name,
            ])))
                ->from($request->user())
                ->link(route('marketplace.resources.show', $resource, false))
                ->send($resource->author);
        }

        return back()->with('success', trans('marketplace::messages.comment_added'));
    }

    public function destroy(Request $request, Comment $comment)
    {
        abort_unless(
            $comment->user_id === $request->user()->id
            || $request->user()->can('marketplace.delete-comments'),
            403
        );

        $comment->delete();

        return back()->with('success', trans('messages.status.success'));
    }
}
