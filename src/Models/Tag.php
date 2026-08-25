<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = ['name', 'slug', 'description', 'color', 'position', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'marketplace_resource_tag')
            ->withoutGlobalScope('notArchived');
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }
}
