<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $table = "ry_admin_user_preferences";
    
    protected $fillable = ['data'];
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope("latest", function($builder){
            $builder->orderBy("updated_at", "desc");
        });
    }
    
    public function setArdataAttribute($data) {
        $this->data = json_encode($data);
    }
    
    public function getArdataAttribute() {
        return json_decode($this->data, true);
    }
}
