<?php
namespace Azuriom\Plugin\Marketplace\Controllers;
use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;
class RatingController extends Controller { public function store(Request $request,Resource $resource){abort_unless($resource->status==='published'&&$resource->category->canAccess($request->user())&&$resource->isUnlockedBy($request->user()),403);$data=$request->validate(['rating'=>['required','integer','between:1,5']]);$resource->ratings()->updateOrCreate(['user_id'=>$request->user()->id],$data);return back()->with('success',trans('marketplace::messages.rating_saved'));} }
