<?php

namespace Ry\Admin\Models\Layout;

use Illuminate\Database\Eloquent\Model;

class LayoutSection extends Model
{
    protected $table = "ry_admin_layout_sections";
    
    protected $fillable = [
        'name', 'default_setup', 'active'
    ];
    protected $casts = [
        'active' => 'bool'
    ];
    
    public function layout() {
        return $this->belongsTo(Layout::class, "layout_id");
    }
}
