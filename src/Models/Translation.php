<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = "ry_admin_translations";
    
    protected $fillable = ['code'];
    
    protected $with = ["meanings"];
    
    private static $cache = [];
    
    private static $meaning = [];
    
    public function meanings() {
        return $this->hasMany(LanguageTranslation::class, "translation_id");
    }
    
    public function getStringsAttribute() {
        if(!isset(self::$cache[$this->id]))
            self::$cache[$this->id] = $this->meanings()->pluck('translation_string', 'lang');
        return self::$cache[$this->id];
    }
    
    public function getMeaningAttribute() {
        if(!isset(self::$meaning[$this->id]))
            self::$meaning[$this->id] = $this->meanings()->current()->first();
        return self::$meaning[$this->id];
    }
}
