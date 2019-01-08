<?php
namespace Ry\Admin\Models\Traits;

use Ry\Admin\Models\Role;

trait AdministratorTrait
{
	public function isAdmin() {
		return $this->roles()->where("name", "=", "admin")->exists();
	}
	
	public function roles() {
	    return $this->belongsToMany(Role::class, 'ry_admin_user_roles', 'user_id', 'role_id');
	}
	
	
}