<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class Timeline extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_admin_timelines";
    
    protected $appends = ['nsetup'];
    
    protected $fillable = ['active'];
    
    protected $dates = ['save_at', 'delete_at'];
    
    public function serializable() {
        return $this->morphTo();
    }
    
    public function reversion() {
        return $this->belongsTo(Timeline::class, 'revert_id');
    }
}
