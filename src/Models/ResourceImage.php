<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceImage extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = [
        'resource_id', 'user_id', 'draft_token', 'path', 'mime_type', 'size', 'width', 'height',
    ];

    protected static function booted(): void
    {
        static::creating(function (ResourceImage $image) {
            $image->uuid ??= (string) Str::uuid();
        });

        static::deleted(function (ResourceImage $image) {
            Storage::disk('local')->delete($image->path);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
