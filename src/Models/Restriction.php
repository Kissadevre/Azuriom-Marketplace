<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasTablePrefix;

    public const ACTION_COMMENT = 'comment';
    public const ACTION_PUBLISH = 'publish';
    public const ACTION_EDIT = 'edit';
    public const ACTION_UPDATE = 'update';

    protected string $prefix = 'marketplace_';

    protected $fillable = [
        'user_id',
        'created_by',
        'lifted_by',
        'actions',
        'reason',
        'expires_at',
        'lifted_at',
    ];

    protected $casts = [
        'actions' => 'array',
        'expires_at' => 'datetime',
        'lifted_at' => 'datetime',
    ];

    public static function actions(): array
    {
        return [
            self::ACTION_COMMENT,
            self::ACTION_PUBLISH,
            self::ACTION_EDIT,
            self::ACTION_UPDATE,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function liftedBy()
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('lifted_at')
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public static function activeFor(User $user, string $action): ?self
    {
        return self::query()
            ->active()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->first(fn (self $restriction) => in_array($action, $restriction->actions, true));
    }
}
