<?php
namespace Ry\Admin\Models\Traits;

trait AdministratorTrait
{
	public function isAdmin() {
		return $this->roles()->where("name", "=", "admin")->exists();
	}
	
	public function roles() {
		return $this->hasMany("Ry\Admin\Models\Role", "user_id");
	}
}