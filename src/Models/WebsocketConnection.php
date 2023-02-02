<?php

namespace Ry\Admin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsocketConnection extends Model
{
    use HasFactory;

    protected $table = 'ry_admin_wsconnections';

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
