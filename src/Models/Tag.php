<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Azuriom\Models\User;

class Tag extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = ['category_id', 'publish_roles', 'name', 'slug', 'description', 'color', 'position', 'is_enabled'];

    protected $casts = ['category_id' => 'integer', 'publish_roles' => 'array', 'is_enabled' => 'boolean'];

    public function canUse(?User $user): bool { return $this->publish_roles === null || ($user !== null && in_array($user->role_id, $this->publish_roles, true)); }
    public function setPublishRolesAttribute(?array $roles): void { $this->attributes['publish_roles'] = $roles === null ? null : json_encode(array_map('intval', $roles)); }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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
