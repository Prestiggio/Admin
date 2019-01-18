<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = "ry_admin_translations";
    
    protected $fillable = ['code'];
    
    protected $appends = ['strings'];
    
    public function meanings() {
        return $this->hasMany(LanguageTranslation::class, "translation_id");
    }
    
    public function getStringsAttribute() {
        return $this->meanings()->pluck('translation_string', 'lang');
    }
}
