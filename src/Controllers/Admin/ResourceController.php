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
}
