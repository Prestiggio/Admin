<?php

namespace Ry\Admin\Models\Layout;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Role;

class LayoutSection extends Model
{
    protected $table = "ry_admin_layout_sections";
    
    protected $hidden = ['created_at', 'updated_at', 'default_setup'];
    
    protected $fillable = [
        'name', 'active'
    ];
    protected $casts = [
        'active' => 'bool'
    ];
    
    protected $appends = ['setup'];
    
    public function layout() {
        return $this->belongsTo(Layout::class, "layout_id");
    }
    
    public function getSetupAttribute() {
        return json_decode($this->default_setup, true);
    }
    
    public function setSetupAttribute($data) {
        $this->default_setup = json_encode($data);
    }
}
