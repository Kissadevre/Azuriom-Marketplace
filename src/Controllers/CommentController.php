<?php
namespace Azuriom\Plugin\Marketplace\Controllers;
use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;
class CommentController extends Controller {
 public function store(Request $request, Resource $resource){abort_unless($resource->status==='published'&&$resource->category->canAccess($request->user())&&$resource->isUnlockedBy($request->user()),403);$data=$request->validate(['content'=>['required','string','max:5000']]);$resource->comments()->create($data+['user_id'=>$request->user()->id]);return back()->with('success',trans('marketplace::messages.comment_added'));}
 public function destroy(Request $request,Comment $comment){abort_unless($comment->user_id===$request->user()->id||$request->user()->can('marketplace.admin'),403);$comment->delete();return back()->with('success',trans('messages.status.success'));}
}
