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

        $resources = Resource::query()
            ->with(['category', 'author'])
            ->withAvg('ratings', 'rating')
            ->when(! $canModerate, fn (Builder $query) => $query
                ->published()
                ->whereIn('category_id', $categories->pluck('id')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('summary', 'like', '%'.$request->string('search').'%')
            ))
            ->when($canModerate, fn (Builder $query) => $query->latest('updated_at'), fn (Builder $query) => $query->latest('published_at'))
            ->paginate(12)
            ->withQueryString();

        return view('marketplace::index', compact('categories', 'resources', 'canModerate'));
    }

    public function category(Request $request, Category $category)
    {
        $canModerate = $request->user()?->can('marketplace.moderate') ?? false;
        abort_unless($category->is_enabled && ($canModerate || $category->canAccess($request->user())), 403);

        $resources = $category->resources()
            ->when(! $canModerate, fn (Builder $query) => $query->published())
            ->with('author')
            ->withAvg('ratings', 'rating')
            ->when($canModerate, fn (Builder $query) => $query->latest('updated_at'), fn (Builder $query) => $query->latest('published_at'))
            ->paginate(12);
        $categories = $this->categories($request, $canModerate);

        return view('marketplace::index', compact('categories', 'resources', 'category', 'canModerate'));
    }

    private function categories(Request $request, bool $canModerate)
    {
        $categories = Category::enabled()->orderBy('position')->get();

        return $canModerate ? $categories : $categories->filter->canAccess($request->user());
    }
}
