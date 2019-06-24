<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Ry\Admin\Models\Traits\HasJsonSetup;

class Model extends BaseModel
{
    use HasJsonSetup;
    
    protected $table = "ry_admin_models";
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope('alpha', function($q){
            $q->orderBy('qualified_name');
        });
    }
}
