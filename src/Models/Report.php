<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = [
        'user_id',
        'reportable_type',
        'reportable_id',
        'subject',
        'excerpt',
        'reason',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reportable()
    {
        return $this->morphTo();
    }
}
