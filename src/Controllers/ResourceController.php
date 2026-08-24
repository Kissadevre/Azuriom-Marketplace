<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Purchase;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Requests\ResourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    public function show(Request $request, Resource $resource)
    {
        abort_unless(($resource->status === 'published' && $resource->category->canAccess($request->user())) || $resource->isOwnedBy($request->user()) || $request->user()?->can('marketplace.admin'), 403);
        $resource->load(['author', 'category', 'comments.user', 'ratings']);
        return view('marketplace::resources.show', compact('resource'));
    }

    public function create(Request $request)
    {
        return view('marketplace::resources.create', ['categories' => $this->categories($request)]);
    }

    public function store(ResourceRequest $request)
    {
        $data = $this->payload($request);
        abort_unless(Category::findOrFail($data['category_id'])->canAccess($request->user()), 403);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['status'] = $this->requiresModeration($request) ? 'pending' : 'published';
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $resource = Resource::create($data);
        return to_route('marketplace.resources.show', $resource)->with('success', trans('marketplace::messages.saved'));
    }

    public function edit(Request $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()), 403);
        return view('marketplace::resources.edit', ['resource' => $resource, 'categories' => $this->categories($request)]);
    }

    public function update(ResourceRequest $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()), 403);
        $data = $this->payload($request, $resource);
        abort_unless(Category::findOrFail($data['category_id'])->canAccess($request->user()), 403);
        $data['status'] = $this->requiresModeration($request) ? 'pending' : 'published';
        $data['published_at'] = $data['status'] === 'published'
            ? ($resource->published_at ?? now())
            : null;
        $resource->update($data);
        return to_route('marketplace.resources.show', $resource)->with('success', trans('marketplace::messages.saved'));
    }

    public function destroy(Request $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()) || $request->user()->can('marketplace.admin'), 403);
        if ($resource->file_path) Storage::disk('local')->delete($resource->file_path);
        $resource->delete();
        return to_route('marketplace.index')->with('success', trans('messages.status.success'));
    }

    public function purchase(Request $request, Resource $resource)
    {
        abort_unless($resource->status === 'published' && $resource->category->canAccess($request->user()), 403);
        if ($resource->isUnlockedBy($request->user())) return back()->with('success', trans('marketplace::messages.already_unlocked'));
        DB::transaction(function () use ($request, $resource) {
            $users = User::query()->whereIn('id', [$request->user()->id, $resource->user_id])
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $buyer = $users->get($request->user()->id);
            $seller = $users->get($resource->user_id);
            abort_unless($buyer && $seller, 404);
            if (Purchase::where('resource_id', $resource->id)->where('user_id', $buyer->id)->exists()) return;
            if ($buyer->money < $resource->price) throw ValidationException::withMessages(['purchase' => trans('marketplace::messages.insufficient_money')]);
            $buyer->removeMoney($resource->price);
            $seller->addMoney($resource->price);
            Purchase::create(['resource_id'=>$resource->id,'user_id'=>$buyer->id,'price'=>$resource->price]);
        });
        return back()->with('success', trans('marketplace::messages.purchased'));
    }

    public function download(Request $request, Resource $resource)
    {
        abort_unless($resource->status === 'published' && $resource->category->canAccess($request->user()) && $resource->isUnlockedBy($request->user()), 403);

        if ($resource->delivery_type === 'external') {
            $destination = $this->externalDestination($resource);

            return view('marketplace::resources.external', [
                'resource' => $resource,
                'destinationHost' => $destination['host'],
            ]);
        }

        $resource->increment('downloads');
        abort_unless($resource->file_path && Storage::disk('local')->exists($resource->file_path), 404);

        return Storage::disk('local')->download($resource->file_path, basename($resource->file_path));
    }

    public function continueExternal(Request $request, Resource $resource)
    {
        abort_unless(
            $resource->status === 'published'
            && $resource->category->canAccess($request->user())
            && $resource->isUnlockedBy($request->user()),
            403
        );

        $destination = $this->externalDestination($resource);
        $resource->increment('downloads');

        return redirect()->away($destination['url']);
    }

    private function categories(Request $request) { return Category::enabled()->orderBy('position')->get()->filter->canAccess($request->user()); }
    private function payload(ResourceRequest $request, ?Resource $resource = null): array
    {
        $data = $request->safe()->except('file');
        if ($request->hasFile('file')) {
            if ($resource?->file_path) Storage::disk('local')->delete($resource->file_path);
            $data['file_path'] = $request->file('file')->store('marketplace/resources', 'local');
        }
        if ($data['delivery_type'] === 'file') $data['external_url'] = null;
        else { if ($resource?->file_path) Storage::disk('local')->delete($resource->file_path); $data['file_path'] = null; }
        return $data;
    }
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'resource'; $slug = $base; $i = 2;
        while (Resource::where('slug', $slug)->exists()) $slug = $base.'-'.$i++;
        return $slug;
    }

    private function requiresModeration(Request $request): bool
    {
        return (bool) setting('marketplace.moderation', true)
            && ! $request->user()->can('marketplace.bypass-moderation');
    }

    /**
     * @return array{url: string, host: string}
     */
    private function externalDestination(Resource $resource): array
    {
        abort_unless($resource->delivery_type === 'external', 404);

        $url = $resource->external_url;
        $scheme = is_string($url) ? parse_url($url, PHP_URL_SCHEME) : null;
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;

        abort_unless(in_array($scheme, ['http', 'https'], true) && is_string($host) && $host !== '', 404);

        return ['url' => $url, 'host' => $host];
    }
}
