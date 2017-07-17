<?php namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	protected $table = "ry_admin_roles";
	
	public function user() {
		return $this->belongsTo("App\User", "user_id");
	}

}
