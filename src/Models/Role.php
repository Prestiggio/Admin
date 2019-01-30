<?php namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\RoleLayout;

class Role extends Model {

	protected $table = "ry_admin_roles";
	
	protected $fillable = [
	    'name', 'active', 'level'
	];
	protected $casts = [
	    'active' => 'bool',
	    'level' => 'int',
	];
	protected $hidden = ['created_at', 'updated_at'];
	
	public function users() {
		return $this->belongsToMany(User::class, "ry_admin_user_roles", "role_id", "user_id");
	}
	
	public function permissions() {
	    return $this->belongsToMany(Permission::class, 'ry_admin_role_permissions', 'role_id', 'permission_id');
	}
	
	public function layouts() {
	    return $this->belongsToMany(Layout::class, "ry_admin_role_layouts", "role_id", "layout_id");
	}
	
	public function layoutOverrides() {
	    return $this->hasMany(RoleLayout::class);
	}
	
	public function userRoles() {
	    return $this->hasMany(UserRole::class, "role_id");
	}

}
