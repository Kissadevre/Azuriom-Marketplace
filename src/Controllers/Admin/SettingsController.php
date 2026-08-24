<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Marketplace\Models\Purchase;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('marketplace::admin.settings', [
            'publishedResources' => Resource::published()->count(),
            'pendingResources' => Resource::where('status', 'pending')->count(),
            'spentPoints' => (float) Purchase::sum('price'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'max_file_size' => ['required', 'integer', 'min:1', 'max:1048576'],
        ]);

        Setting::updateSettings([
            'marketplace.moderation' => $request->boolean('moderation'),
            'marketplace.max_file_size' => $data['max_file_size'],
        ]);

        return back()->with('success', trans('messages.status.success'));
    }
}
