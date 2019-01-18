<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;

class LanguageTranslation extends Model
{
    protected $table = "ry_admin_language_translations";
    
    public $timestamps = false;
    
    protected $fillable = ['translation_string', 'lang'];
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope('frfirst', function($q){
            $q->orderByRaw("FIELD(lang, 'fr') DESC");
        });
    }
    
    public function slug() {
        return $this->belongsTo(Translation::class, "translation_id");
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
