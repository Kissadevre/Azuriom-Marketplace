<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $canModerate = $request->user()?->can('marketplace.moderate') ?? false;
        $categories = $this->categories($request, $canModerate);
        $sort = $this->sort($request);
        $myResourcesCount = $this->myResourcesCount($request);
        $mine = false;

        $query = Resource::query()
            ->with(['category', 'author', 'tags', 'latestUpdate'])
            ->withAvg('ratings', 'rating')
            ->when(! $canModerate, fn (Builder $query) => $query
                ->published()
                ->whereIn('category_id', $categories->pluck('id')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('summary', 'like', '%'.$request->string('search').'%')
            ));

        $resources = $this->applySorting($query, $sort)
            ->paginate(12)
            ->withQueryString();

        return view('marketplace::index', compact('categories', 'resources', 'canModerate', 'sort', 'myResourcesCount', 'mine'));
    }

    public function category(Request $request, Category $category)
    {
        $canModerate = $request->user()?->can('marketplace.moderate') ?? false;
        $sort = $this->sort($request);
        $myResourcesCount = $this->myResourcesCount($request);
        $mine = false;
        abort_unless($category->is_enabled && ($canModerate || $category->canAccess($request->user())), 403);

        $query = Resource::query()
            ->where('category_id', $category->id)
            ->when(! $canModerate, fn (Builder $query) => $query->published())
            ->with(['author', 'tags', 'latestUpdate'])
            ->withAvg('ratings', 'rating');
        $resources = $this->applySorting($query, $sort)->paginate(12)->withQueryString();
        $categories = $this->categories($request, $canModerate);

        return view('marketplace::index', compact('categories', 'resources', 'category', 'canModerate', 'sort', 'myResourcesCount', 'mine'));
    }

    public function mine(Request $request)
    {
        $canModerate = $request->user()->can('marketplace.moderate');
        $categories = $this->categories($request, $canModerate);
        $sort = $this->sort($request);
        $myResourcesCount = $this->myResourcesCount($request);
        $mine = true;

        $query = Resource::query()
            ->where('user_id', $request->user()->id)
            ->with(['category', 'author', 'tags', 'latestUpdate'])
            ->withAvg('ratings', 'rating')
            ->when($request->filled('search'), fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('summary', 'like', '%'.$request->string('search').'%')
            ));

        $resources = $this->applySorting($query, $sort)
            ->paginate(12)
            ->withQueryString();

        return view('marketplace::index', compact('categories', 'resources', 'canModerate', 'sort', 'myResourcesCount', 'mine'));
    }

    private function categories(Request $request, bool $canModerate)
    {
        $categories = Category::enabled()
            ->withCount([
                'resources' => fn (Builder $query) => $query
                    ->when(! $canModerate, fn (Builder $query) => $query->published()),
            ])
            ->orderBy('position')
            ->get();

        return $canModerate ? $categories : $categories->filter->canAccess($request->user());
    }

    private function sort(Request $request): string
    {
        $sort = $request->string('sort')->toString();

        return in_array($sort, ['updated', 'downloads', 'rating'], true) ? $sort : 'updated';
    }

    private function myResourcesCount(Request $request): int
    {
        return $request->user() === null
            ? 0
            : Resource::query()->where('user_id', $request->user()->id)->count();
    }

    private function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'downloads' => $query
                ->orderByDesc('marketplace_resources.downloads')
                ->orderByDesc('marketplace_resources.published_at')
                ->orderByDesc('marketplace_resources.id'),
            'rating' => $query
                ->orderByRaw('COALESCE((SELECT AVG(mrr.rating) FROM marketplace_ratings AS mrr WHERE mrr.resource_id = marketplace_resources.id), 0) DESC')
                ->orderByDesc('marketplace_resources.downloads')
                ->orderByDesc('marketplace_resources.id'),
            default => $query
                ->orderByRaw('COALESCE((SELECT MAX(mru.created_at) FROM marketplace_resource_updates AS mru WHERE mru.resource_id = marketplace_resources.id), marketplace_resources.published_at, marketplace_resources.created_at) DESC')
                ->orderByDesc('marketplace_resources.id'),
        };
    }
}
