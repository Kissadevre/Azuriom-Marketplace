<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Purchase;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Requests\ResourceRequest;
use Azuriom\Plugin\Marketplace\Support\ResourceHtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    public function show(Request $request, Resource $resource)
    {
        abort_unless($this->canView($request, $resource), 403);
        $resource->load(['author', 'category', 'comments.user', 'ratings', 'updates.author', 'latestUpdate']);

        $categories = Category::enabled()->get();
        if (! $this->hasResourceToolPermission($request)) {
            $categories = $categories->filter->canAccess($request->user());
        }

        $relatedResources = Resource::published()
            ->where('user_id', $resource->user_id)
            ->whereKeyNot($resource->id)
            ->whereIn('category_id', $categories->pluck('id'))
            ->with(['category', 'latestUpdate'])
            ->withAvg('ratings', 'rating')
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('marketplace::resources.show', compact('resource', 'relatedResources'));
    }

    public function banner(Request $request, Resource $resource)
    {
        abort_unless($this->canView($request, $resource), 403);
        abort_unless(
            $resource->banner_path
            && Storage::disk('local')->exists($resource->banner_path),
            404
        );

        return Storage::disk('local')->response(
            $resource->banner_path,
            null,
            ['Cache-Control' => 'private, max-age=3600']
        );
    }

    public function create(Request $request)
    {
        abort_if(
            setting('marketplace.pause_submissions', false),
            403,
            trans('marketplace::messages.submissions_paused')
        );

        return view('marketplace::resources.create', ['categories' => $this->categories($request)]);
    }

    public function store(ResourceRequest $request)
    {
        abort_if(
            setting('marketplace.pause_submissions', false),
            403,
            trans('marketplace::messages.submissions_paused')
        );

        abort_unless(
            Category::findOrFail($request->integer('category_id'))->canAccess($request->user()),
            403
        );
        $data = $this->payload($request);
        $data['user_id'] = $request->user()->id;
        $data['status'] = $this->requiresModeration($request) ? 'pending' : 'published';
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $resource = Resource::create($data);
        return to_route('marketplace.resources.show', $resource)->with('success', trans('marketplace::messages.saved'));
    }

    public function edit(Request $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()) || $request->user()->can('marketplace.edit'), 403);
        return view('marketplace::resources.edit', ['resource' => $resource, 'categories' => $this->categories($request)]);
    }

    public function update(ResourceRequest $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()) || $request->user()->can('marketplace.edit'), 403);
        abort_unless(
            $request->user()->can('marketplace.edit')
            || Category::findOrFail($request->integer('category_id'))->canAccess($request->user()),
            403
        );
        $data = $this->payload($request, $resource);
        $data['status'] = $this->requiresModeration($request) ? 'pending' : 'published';
        $data['published_at'] = $data['status'] === 'published'
            ? ($resource->published_at ?? now())
            : null;
        $resource->update($data);
        return to_route('marketplace.resources.show', $resource)->with('success', trans('marketplace::messages.saved'));
    }

    public function destroy(Request $request, Resource $resource)
    {
        abort_unless($resource->isOwnedBy($request->user()) || $request->user()->can('marketplace.delete'), 403);
        Storage::disk('local')->delete(array_filter([$resource->file_path, $resource->banner_path]));
        $resource->delete();
        return to_route('marketplace.index')->with('success', trans('messages.status.success'));
    }

    public function purchase(Request $request, Resource $resource)
    {
        abort_unless($resource->status === 'published' && ! $resource->isPaused() && $resource->category->canAccess($request->user()), 403);
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
        abort_unless($this->canDownload($request, $resource), 403);

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
            $this->canDownload($request, $resource),
            403
        );

        $destination = $this->externalDestination($resource);
        $resource->increment('downloads');

        return redirect()->away($destination['url']);
    }

    private function categories(Request $request)
    {
        $categories = Category::enabled()->orderBy('position')->get();

        return $request->user()->can('marketplace.edit')
            ? $categories
            : $categories->filter->canAccess($request->user());
    }
    private function payload(ResourceRequest $request, ?Resource $resource = null): array
    {
        $data = $request->safe()->except(['file', 'banner', 'remove_banner']);
        $data['description'] = app(ResourceHtmlSanitizer::class)->sanitize($data['description']);

        if ($data['description'] === '') {
            throw ValidationException::withMessages([
                'description' => trans('validation.required', [
                    'attribute' => trans('marketplace::messages.fields.description'),
                ]),
            ]);
        }

        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('marketplace/banners', 'local');
            if ($resource?->banner_path) {
                Storage::disk('local')->delete($resource->banner_path);
            }
            $data['banner_path'] = $bannerPath;
        } elseif ($request->boolean('remove_banner') && $resource?->banner_path) {
            Storage::disk('local')->delete($resource->banner_path);
            $data['banner_path'] = null;
        }

        if ($request->hasFile('file')) {
            if ($resource?->file_path) Storage::disk('local')->delete($resource->file_path);
            $data['file_path'] = $request->file('file')->store('marketplace/resources', 'local');
        }
        if ($data['delivery_type'] === 'file') $data['external_url'] = null;
        else { if ($resource?->file_path) Storage::disk('local')->delete($resource->file_path); $data['file_path'] = null; }
        return $data;
    }
    private function requiresModeration(Request $request): bool
    {
        return (bool) setting('marketplace.moderation', true)
            && ! $request->user()->can('marketplace.bypass-moderation');
    }

    private function canDownload(Request $request, Resource $resource): bool
    {
        return $resource->status === 'published'
            && ! $resource->isPaused()
            && $resource->category->canAccess($request->user())
            && ($resource->isUnlockedBy($request->user())
                || $request->user()->can('marketplace.download-paid'));
    }

    private function hasResourceToolPermission(Request $request): bool
    {
        if ($request->user() === null) {
            return false;
        }

        return collect([
            'marketplace.admin',
            'marketplace.moderate',
            'marketplace.archive',
            'marketplace.pause',
            'marketplace.edit',
            'marketplace.delete',
            'marketplace.delete-comments',
            'marketplace.reset-ratings',
        ])->contains(fn (string $permission) => $request->user()->can($permission));
    }

    private function canView(Request $request, Resource $resource): bool
    {
        return ($resource->status === 'published' && $resource->category->canAccess($request->user()))
            || $resource->isOwnedBy($request->user())
            || $this->hasResourceToolPermission($request);
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
