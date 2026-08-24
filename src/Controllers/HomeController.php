<?php

namespace Azuriom\Plugin\Marketplace\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::enabled()->orderBy('position')->get()->filter->canAccess($request->user());
        $resources = Resource::published()->with(['category', 'author'])->withAvg('ratings', 'rating')
            ->whereIn('category_id', $categories->pluck('id'))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%')->orWhere('summary', 'like', '%'.$request->string('search').'%')))
            ->latest('published_at')->paginate(12)->withQueryString();
        return view('marketplace::index', compact('categories', 'resources'));
    }

    public function category(Request $request, Category $category)
    {
        abort_unless($category->is_enabled && $category->canAccess($request->user()), 403);
        $resources = $category->resources()->published()->with('author')->withAvg('ratings', 'rating')->latest('published_at')->paginate(12);
        $categories = Category::enabled()->orderBy('position')->get()->filter->canAccess($request->user());
        return view('marketplace::index', compact('categories', 'resources', 'category'));
    }
}
