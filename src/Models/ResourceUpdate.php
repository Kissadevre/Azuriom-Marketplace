<?php

namespace Azuriom\Plugin\Marketplace\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class ResourceUpdate extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'marketplace_';

    protected $fillable = ['resource_id', 'user_id', 'version', 'description'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
