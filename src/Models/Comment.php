<?php
namespace Azuriom\Plugin\Marketplace\Models;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;
class Comment extends Model { use HasTablePrefix; protected string $prefix = 'marketplace_'; protected $fillable = ['resource_id','user_id','content']; public function resource(){return $this->belongsTo(Resource::class);} public function user(){return $this->belongsTo(User::class);} public function likes(){return $this->hasMany(CommentLike::class);} public function reports(){return $this->morphMany(Report::class, 'reportable');} }
