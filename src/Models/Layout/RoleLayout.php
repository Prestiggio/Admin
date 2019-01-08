<?php

namespace Ry\Admin\Models\Layout;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Role;

class RoleLayout extends Model
{
    protected $table = "ry_admin_role_layouts";
    
    protected $fillable = [
        'sections_setup'
    ];
    
    public function layout() {
        return $this->belongsTo(Layout::class, "layout_id");
    }
    
    public function role() {
        return $this->belongsTo(Role::class, "role_id");
    }
}
