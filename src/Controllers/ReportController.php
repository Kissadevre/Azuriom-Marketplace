<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function resource(Request $request, Resource $resource)
    {
        abort_unless(
            $resource->status === 'published'
            && $resource->category->canAccess($request->user()),
            403
        );
        abort_if($resource->isOwnedBy($request->user()), 403);

        return $this->store(
            $request,
            $resource,
            $resource->name,
            $resource->summary
        );
    }

    public function comment(Request $request, Comment $comment)
    {
        abort_unless(
            $comment->resource->status === 'published'
            && $comment->resource->category->canAccess($request->user()),
            403
        );
        abort_if($comment->user_id === $request->user()->id, 403);

        return $this->store(
            $request,
            $comment,
            trans('marketplace::messages.reports.comment_by', ['user' => $comment->user->name]),
            $comment->content
        );
    }

    private function store(Request $request, Model $reportable, string $subject, ?string $excerpt)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $report = $reportable->reports()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'subject' => Str::limit($subject, 255, ''),
                'excerpt' => Str::limit((string) $excerpt, 2000),
                'reason' => $data['reason'],
            ]
        );

        return back()->with(
            $report->wasRecentlyCreated ? 'success' : 'warning',
            trans($report->wasRecentlyCreated
                ? 'marketplace::messages.reports.sent'
                : 'marketplace::messages.reports.already_sent')
        );
    }
}
