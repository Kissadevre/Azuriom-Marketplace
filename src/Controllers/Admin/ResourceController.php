<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Resource;

class ResourceController extends Controller
{
    public function pending()
    {
        return view('marketplace::admin.resources.pending', [
            'resources' => Resource::where('status', 'pending')
                ->with('author')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function archived()
    {
        return view('marketplace::admin.resources.archived', [
            'resources' => Resource::query()
                ->withoutGlobalScope('notArchived')
                ->whereNotNull('archived_at')
                ->with(['author', 'category'])
                ->latest('archived_at')
                ->paginate(20),
        ]);
    }

    public function restore(string $resourceUuid)
    {
        $resource = Resource::query()
            ->withoutGlobalScope('notArchived')
            ->whereNotNull('archived_at')
            ->where('uuid', $resourceUuid)
            ->firstOrFail();

        $resource->update(['archived_at' => null]);

        return back()->with('success', trans('marketplace::admin.archived_resources.restored'));
    }
}
