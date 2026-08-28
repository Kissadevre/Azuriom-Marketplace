<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Category;
use Azuriom\Plugin\Marketplace\Models\Tag;
use Azuriom\Plugin\Marketplace\Requests\TagRequest;

class TagController extends Controller
{
    public function index()
    {
        return view('marketplace::admin.tags.index', [
            'tags' => Tag::with('category')->withCount('resources')->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('marketplace::admin.tags.create', [
            'categories' => Category::orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function store(TagRequest $request)
    {
        Tag::create($request->validated());

        return to_route('marketplace.admin.tags.index')
            ->with('success', trans('messages.status.success'));
    }

    public function edit(Tag $tag)
    {
        return view('marketplace::admin.tags.edit', [
            'tag' => $tag,
            'categories' => Category::orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return to_route('marketplace.admin.tags.index')
            ->with('success', trans('messages.status.success'));
    }

    public function destroy(Tag $tag)
    {
        abort_if(
            $tag->resources()->exists(),
            422,
            trans('marketplace::admin.tags.not_empty')
        );

        $tag->delete();

        return back()->with('success', trans('messages.status.success'));
    }
}
