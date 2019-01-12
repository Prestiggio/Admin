<?php

namespace Ry\Admin\Models\Layout;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Role;

class Layout extends Model
{
    protected $table = "ry_admin_layouts";
    
    protected $fillable = [
        'name'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    public function sublayouts() {
        return $this->hasMany(RoleLayout::class, "layout_id");
    }
    
    public function roles() {
        return $this->belongsToMany(Role::class, "ry_admin_role_layouts", "layout_id", "role_id");
    }
    
    public function sections() {
        return $this->hasMany(LayoutSection::class, "layout_id");
    }
}
