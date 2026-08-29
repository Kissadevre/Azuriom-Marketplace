<?php
namespace Azuriom\Plugin\Marketplace\Models;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;
class GiftCode extends Model { use HasTablePrefix; protected string $prefix='marketplace_'; protected $fillable=['user_id','code_hash','code_hint','usage_limit','expires_at']; protected $casts=['expires_at'=>'datetime','usage_limit'=>'integer']; public function author(){return $this->belongsTo(User::class,'user_id');} public function resources(){return $this->belongsToMany(Resource::class,'marketplace_gift_code_resource');} public function redemptions(){return $this->hasMany(GiftCodeRedemption::class);} }
