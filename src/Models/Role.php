<?php namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Role extends Model {

	protected $table = "ry_admin_roles";
	
	protected $fillable = [
	    'name', 'active', 'level'
	];
	protected $casts = [
	    'active' => 'bool',
	    'level' => 'int',
	];
	
	public function users() {
		return $this->belongsToMany(User::class, "ry_admin_user_roles", "role_id", "user_id");
	}
	
	public function permissions() {
	    return $this->belongsToMany(Permission::class, 'ry_admin_role_permissions', 'role_id', 'permission_id');
	}

}
