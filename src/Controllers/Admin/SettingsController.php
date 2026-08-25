<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Marketplace\Models\Purchase;
use Azuriom\Plugin\Marketplace\Models\Resource;
use Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(ResourceFilePolicy $filePolicy)
    {
        return view('marketplace::admin.settings', [
            'publishedResources' => Resource::published()->count(),
            'pendingResources' => Resource::where('status', 'pending')->count(),
            'spentPoints' => (float) Purchase::sum('price'),
            'allowedExtensions' => implode(', ', $filePolicy->allowedExtensions()),
            'forbiddenExtensions' => ResourceFilePolicy::FORBIDDEN,
        ]);
    }

    public function update(Request $request, ResourceFilePolicy $filePolicy)
    {
        $data = $request->validate([
            'max_file_size' => ['required', 'integer', 'min:1', 'max:1048576'],
            'max_editor_image_size' => ['required', 'integer', 'min:100', 'max:20480'],
            'max_editor_images' => ['required', 'integer', 'min:1', 'max:100'],
            'allowed_extensions' => [
                'required',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail) use ($filePolicy) {
                    $extensions = $filePolicy->parse((string) $value);

                    if ($extensions === []) {
                        $fail(trans('marketplace::admin.settings.allowed_extensions_required'));
                    }

                    if ($invalid = $filePolicy->invalid($extensions)) {
                        $fail(trans('marketplace::admin.settings.allowed_extensions_invalid', [
                            'extensions' => implode(', ', $invalid),
                        ]));
                    }

                    if ($forbidden = $filePolicy->forbidden($extensions)) {
                        $fail(trans('marketplace::admin.settings.allowed_extensions_forbidden', [
                            'extensions' => implode(', ', $forbidden),
                        ]));
                    }
                },
            ],
        ]);

        $allowedExtensions = $filePolicy->parse($data['allowed_extensions']);

        Setting::updateSettings([
            'marketplace.moderation' => $request->boolean('moderation'),
            'marketplace.pause_submissions' => $request->boolean('pause_submissions'),
            'marketplace.pause_comments' => $request->boolean('pause_comments'),
            'marketplace.require_login_for_free_downloads' => $request->boolean('require_login_for_free_downloads'),
            'marketplace.max_file_size' => $data['max_file_size'],
            'marketplace.max_editor_image_size' => $data['max_editor_image_size'],
            'marketplace.max_editor_images' => $data['max_editor_images'],
            'marketplace.allowed_extensions' => implode(',', $allowedExtensions),
        ]);

        return back()->with('success', trans('messages.status.success'));
    }
}
