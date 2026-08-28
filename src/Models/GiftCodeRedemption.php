<?php
namespace Azuriom\Plugin\Marketplace\Models;
use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;
class GiftCodeRedemption extends Model { use HasTablePrefix; protected string $prefix='marketplace_'; protected $fillable=['gift_code_id','user_id']; }
