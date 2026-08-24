<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasTablePrefix;
    protected string $prefix = 'marketplace_';
    protected $fillable = ['category_id', 'user_id', 'name', 'slug', 'version', 'summary', 'description', 'banner_path', 'delivery_type', 'file_path', 'external_url', 'price', 'status', 'moderation_note', 'published_at', 'paused_at', 'archived_at'];
    protected $casts = ['price' => 'float', 'published_at' => 'datetime', 'paused_at' => 'datetime', 'archived_at' => 'datetime'];

    protected static function booted(): void
    {
        static::addGlobalScope('notArchived', fn (Builder $query) => $query->whereNull(
            $query->getModel()->qualifyColumn('archived_at')
        ));
    }

    public function getRouteKeyName(): string { return 'slug'; }
    public function category() { return $this->belongsTo(Category::class); }
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function comments() { return $this->hasMany(Comment::class)->latest(); }
    public function ratings() { return $this->hasMany(Rating::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
    public function updates() { return $this->hasMany(ResourceUpdate::class)->latest(); }
    public function latestUpdate() { return $this->hasOne(ResourceUpdate::class)->latestOfMany(); }
    public function scopePublished(Builder $query): void { $query->where('status', 'published'); }
    public function isOwnedBy(?User $user): bool { return $user !== null && $this->user_id === $user->id; }
    public function isPaused(): bool { return $this->paused_at !== null; }
    public function isUnlockedBy(?User $user): bool
    {
        return $this->price <= 0 || $this->isOwnedBy($user) || ($user !== null && $this->purchases()->where('user_id', $user->id)->exists());
    }
    public function averageRating(): float { return round((float) $this->ratings()->avg('rating'), 1); }
}
