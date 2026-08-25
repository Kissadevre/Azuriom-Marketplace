<?php

namespace Azuriom\Plugin\Marketplace\Commands;

use Azuriom\Plugin\Marketplace\Models\ResourceImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupEditorImages extends Command
{
    protected $signature = 'marketplace:cleanup-editor-images';

    protected $description = 'Delete abandoned and orphaned Marketplace editor images.';

    public function handle(): int
    {
        $deleted = 0;

        ResourceImage::query()
            ->whereNull('resource_id')
            ->where('created_at', '<', now()->subDay())
            ->chunkById(100, function ($images) use (&$deleted) {
                foreach ($images as $image) {
                    $image->delete();
                    $deleted++;
                }
            });

        $knownPaths = ResourceImage::query()->pluck('path')->flip();
        foreach (Storage::disk('local')->files('marketplace/editor-images') as $path) {
            if (! $knownPaths->has($path)
                && Storage::disk('local')->lastModified($path) < now()->subDay()->getTimestamp()) {
                Storage::disk('local')->delete($path);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} abandoned Marketplace editor images.");

        return self::SUCCESS;
    }
}
