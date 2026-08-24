<?php
namespace Azuriom\Plugin\Marketplace\Controllers\Admin;
use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;
class ModerationController extends Controller {
 public function index(){return view('marketplace::admin.moderation',['resources'=>Resource::with(['author','category'])->where('status','pending')->oldest()->paginate()]);}
 public function approve(Resource $resource){$resource->update(['status'=>'published','published_at'=>now(),'moderation_note'=>null]);return back()->with('success',trans('marketplace::admin.moderation.approved'));}
 public function reject(Request $request,Resource $resource){$data=$request->validate(['moderation_note'=>['required','string','max:2000']]);$resource->update(['status'=>'rejected','published_at'=>null]+$data);return back()->with('success',trans('marketplace::admin.moderation.rejected'));}
}
