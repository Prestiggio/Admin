<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;
use App;

class LanguageTranslation extends Model
{
    protected $table = "ry_admin_language_translations";
    
    public $timestamps = false;
    
    protected $fillable = ['translation_string', 'lang'];
    
    protected $appends = ["name"];
    
    public static $exportOnSave = true;
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope('frfirst', function($q){
            $q->orderByRaw("FIELD(lang, 'fr') DESC");
        });
        
        static::saved(function(){
            if(static::$exportOnSave)
                static::export();
        });
    }
    
    public function scopeCurrent($query) {
        return $query->whereLang(App::getLocale());
    }
    
    public function slug() {
        return $this->belongsTo(Translation::class, "translation_id");
    }
    
    public function getNameAttribute() {
        return $this->translation_string;
    }
    
    public static function export() {
        $languages = [];
        $translations = Translation::all();
        foreach($translations as $translation) {
            foreach($translation->meanings as $meaning) {
                if(!isset($languages[$meaning->lang]))
                    $languages[$meaning->lang] = [];
                $languages[$meaning->lang][$translation->code] = $meaning->translation_string;
            }
        }
        $fs = new Filesystem();
        foreach($languages as $lang => $translations) {
            $fs->put(resource_path('lang/'.$lang.".json"), json_encode($translations));
        }
    }
}
