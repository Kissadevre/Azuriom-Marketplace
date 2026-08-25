<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = ['comment_id', 'user_id'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
