<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;

class ResourceFollowController extends Controller
{
    public function toggle(Request $request, Resource $resource)
    {
        abort_unless(
            $resource->status === 'published'
            && $resource->price <= 0
            && ! $resource->isOwnedBy($request->user())
            && $resource->category->canAccess($request->user()),
            403
        );

        $follow = $resource->follows()->where('user_id', $request->user()->id)->first();

        if ($follow) {
            $follow->delete();
            $message = trans('marketplace::messages.follow.removed');
        } else {
            $resource->follows()->create(['user_id' => $request->user()->id]);
            $message = trans('marketplace::messages.follow.added');
        }

        return back()->with('success', $message);
    }
}
