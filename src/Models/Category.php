<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Role;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasTablePrefix;
    protected string $prefix = 'marketplace_';
    protected $fillable = ['name', 'slug', 'icon', 'description', 'roles', 'publish_roles', 'position', 'is_enabled'];
    protected $casts = ['roles' => 'array', 'publish_roles' => 'array', 'is_enabled' => 'boolean'];

    public function resources() { return $this->hasMany(Resource::class); }
    public function tags() { return $this->hasMany(Tag::class); }
    public function scopeEnabled(Builder $query): void { $query->where('is_enabled', true); }
    public function canAccess(?User $user): bool
    {
        return $this->roles === null || ($user !== null && in_array($user->role_id, $this->roles, true));
    }
    public function hasRole(Role $role): bool { return in_array($role->id, $this->roles ?? [], true); }
    public function canPublish(?User $user): bool { return $this->publish_roles === null || ($user !== null && in_array($user->role_id, $this->publish_roles, true)); }
    public function setRolesAttribute(?array $roles): void
    {
        $this->attributes['roles'] = $roles === null ? null : json_encode(array_map('intval', $roles));
    }
    public function setPublishRolesAttribute(?array $roles): void { $this->attributes['publish_roles'] = $roles === null ? null : json_encode(array_map('intval', $roles)); }
}
