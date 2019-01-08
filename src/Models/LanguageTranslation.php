<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;

class LanguageTranslation extends Model
{
    protected $table = "ry_admin_language_translations";
    
    public $timestamps = false;
    
    protected $fillable = ['translation_string'];
    
    public static function writeToFiles() {
        $langs = DB::table("ry_admin_language_translations")->selectRaw("DISTINCT(lang)")->get();
        $locale = \App::getLocale();
        $languages = [];
        $i = 1;
        $fields = [];
        $joins = [];
        foreach($langs as $lang) {
            if($locale != $lang->lang) {
                $languages[$lang->lang] = [];
                $fields[] = "t$i.lang AS idl$i, t$i.translation_string AS trad$i";
                $joins[] = "LEFT JOIN ry_admin_language_translations t$i ON t$i.translation_id = t0.translation_id AND t$i.lang = '".$lang->lang."'";
                $i++;
            }
        }
        $query = "SELECT t0.lang AS idl0, t0.translation_string AS trad0, ".implode(",", $fields)."
                FROM `ry_admin_language_translations` t0
                ".implode(" ", $joins)."
                WHERE t0.lang = 'fr'";
        $rows = DB::select($query);
        $j = 1;
        foreach($rows as $row) {
            for($j=1; $j<$i; $j++) {
                $idl = "idl$j";
                $trad = "trad$j";
                if($row->$idl)
                    $languages[$row->$idl][$row->trad0] = $row->$trad;
            }
        }
        $fs = new Filesystem();
        foreach($languages as $lang => $translations) {
            $fs->put(resource_path('lang/'.$lang.".json"), json_encode($translations));
        }
    }
}
