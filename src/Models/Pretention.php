<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\User;

class Pretention extends Model
{
    protected $table = "ry_admin_pretentions";
    
    protected $dates = ['expire_at'];
    
    protected $fillable = ['used'];
    
    public static function generate($user_id, $pretended_id) {
        static::where('expire_at', '<', Carbon::yesterday())->delete();
        $pretention = static::whereUserId($user_id)->wherePretendedId($pretended_id)->where('expire_at', '>', Carbon::now())->first();
        if(!$pretention) {
            $pretention = new self();
            $pretention->user_id = $user_id;
            $pretention->pretended_id = $pretended_id;
            $pretention->token = str_random(32);
            $pretention->expire_at = Carbon::now()->addMinute(10);
            $pretention->save();
        }
        return $pretention;
    }
    
    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
    
    public function pretended() {
        return $this->belongsTo(User::class, "pretended_id");
    }
}
