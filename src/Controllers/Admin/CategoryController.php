<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Requests\CategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        return view('marketplace::admin.categories.index', [
            'categories' => Category::withCount('resources')->orderBy('position')->get(),
        ]);
    }

    public function create()
    {
        return view('marketplace::admin.categories.create', [
            'roles' => Role::orderByDesc('power')->get(),
        ]);
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return to_route('marketplace.admin.categories.index')
            ->with('success', trans('messages.status.success'));
    }

    public function edit(Category $category)
    {
        return view('marketplace::admin.categories.edit', [
            'category' => $category,
            'roles' => Role::orderByDesc('power')->get(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return to_route('marketplace.admin.categories.index')
            ->with('success', trans('messages.status.success'));
    }

    public function destroy(Category $category)
    {
        abort_if(
            $category->resources()->exists(),
            422,
            trans('marketplace::admin.categories.not_empty')
        );
        $category->delete();

        return back()->with('success', trans('messages.status.success'));
    }
}
