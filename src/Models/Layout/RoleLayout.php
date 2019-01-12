<?php

namespace Ry\Admin\Models\Layout;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Role;

class RoleLayout extends Model
{
    protected $table = "ry_admin_role_layouts";
    
    protected $hidden = ['created_at', 'updated_at', 'sections_setup'];
    
    protected $appends = ['setup'];
    
    public function layout() {
        return $this->belongsTo(Layout::class, "layout_id");
    }
    
    public function role() {
        return $this->belongsTo(Role::class, "role_id");
    }
    
    public function getSetupAttribute() {
        return json_decode($this->sections_setup, true);
    }
    
    public function setSetupAttribute($data) {
        $this->sections_setup = json_encode($data);
    }
}
