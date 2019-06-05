<?php
namespace Ry\Admin\Models\Traits;

use Carbon\Carbon;
use Ry\Admin\Models\Role;

trait AdministratorTrait
{
	public function isAdmin() {
		return $this->roles()->where("name", "=", "admin")->exists();
	}
	
	public function roles() {
	    return $this->belongsToMany(Role::class, 'ry_admin_user_roles', 'user_id', 'role_id')->withPivot('id');
	}
	
	public function getLogoutAttribute() {
	    return route($this->guard . '-logout');
	}
	
	public function getNactivitiesAttribute() {
	    if($this->activities)
	        return json_decode($this->activities, true);
	    return [];
	}
	
	public function log($activity=[]) {
	    if(!is_array($activity))
	        $activity = ["history" => $activity];
        $activities = $this->nactivities;
        if(count($activities)>=10) {
            array_shift($activities);
        }
        $activity['datetime'] = Carbon::now()->format("Y-m-d H:i:s");
        $activities[] = $activity;
        $this->activities = json_encode($activities);
        $this->save();
    }
}
