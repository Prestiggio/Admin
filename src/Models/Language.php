<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Medias\Models\Media;
use Illuminate\Filesystem\Filesystem;
use App;

class Language extends Model
{
    protected $table = "ry_admin_languages";
    
    public $timestamps = false;
    
    protected $with = ['flag'];
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope('pretty', function($q){
            $q->whereHas('flag')->orderByRaw("FIELD(code, '".App::getLocale()."') DESC");
        });
    }
    
    public function flag() {
        return $this->morphOne(Media::class, 'mediable');
    }
    
    public static function export() {
        $fs = new Filesystem();
        $fs->put(storage_path('app/languages.json'), static::withoutGlobalScope("pretty")->get());
    }
}
