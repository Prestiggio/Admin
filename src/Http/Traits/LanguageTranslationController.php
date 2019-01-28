<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ry\Admin\Models\LanguageTranslation;
use Ry\Admin\Models\Permission;
use App, Auth;
use Ry\Admin\Models\Language;
use Ry\Admin\Models\Translation;

trait LanguageTranslationController
{
    public function get_translations(Request $request) {
        $perPage = 10;
        if($request->has("s") && $request->get('s')!='') {
            $translation_query = Translation::whereHas('meanings', function($q)use($request){
                $q->where('translation_string', 'LIKE', '%'.$request->get('s').'%');
            })->orderBy('code');
        }
        else {
            $translation_query = Translation::orderBy('code');
        }
        $rows = $translation_query->paginate($perPage);
        $ar = $rows->toArray();
        $ar['languages'] = DB::table("ry_admin_language_translations")->selectRaw("DISTINCT(lang)")->orderByRaw("FIELD(lang, 'fr') DESC")->pluck("lang");
        $permission = Permission::authorize(__METHOD__);
        return view("$this->theme::traductions", [
            "data" => $ar,
            "page" => [
                "title" => ucfirst(__("traductions")),
                "href" => '/'.request()->path(),
                "icon" => "fa fa-globe-africa",
                "permission" => $permission,
                "children" => []
            ]
        ]);
    }
    
    public function post_translations(Request $request) {
        $translation = LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))
        ->where("lang", "=", $request->get("lang"))->first();
        if(!$translation) {
            $translation = new LanguageTranslation();
            $translation->translation_id = $request->get("translation_id");
            $translation->lang = $request->get("lang");
            $translation->translation_string = $request->get("translation_string");
            $translation->save();
        }
        else {
            LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))
            ->where("lang", "=", $request->get("lang"))->update([
                "translation_string" => $request->get("translation_string")
            ]);
        }
        LanguageTranslation::export();
    }
    
    public function delete_translations(Request $request) {
        Permission::authorize(__METHOD__);
        Translation::find($request->get("translation_id"))->delete();
        LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))->delete();
    }
    
    public function get_translations_add(Request $request) {
        $me = Auth::user();
        $presets = [];
        if($me->preference) {
            foreach($me->preference->ardata["languages_group"] as $k => $v) {
                $presets[] = [
                    "name" => $k,
                    "languages" => $v
                ];
            }
        }
        return view("$this->theme::dialogs.languages", [
            "presets" => [
                "locale" => App::getLocale(),
                "presets" => $presets
            ]
        ]);
    }
    
    public function post_translations_insert(Request $request) {
        $ar = $request->all();
        $presets = [App::getLocale()];
        $me = Auth::user();
        $k = App::getLocale();
        if(!isset($ar['lang'][App::getLocale()])) {
            $lg0 = '';
            foreach($ar['lang'] as $k => $v) {
                $lg0 = $v;
                if($v!='')
                    break;
            }
            $ar['lang'][App::getLocale()] = $lg0;
        }
        if($ar['lang'][App::getLocale()]=='')
            abort(404, __("aucune_traduction_na_ete_soumise"));
        if(LanguageTranslation::where("translation_string", "LIKE", $ar['lang'][App::getLocale()])->exists())
            return abort(409, __("cette_traduction_existe_deja"));
        
        $translation = Translation::create([
            'code' => str_slug($ar['lang'][App::getLocale()], '_', $k)
        ]);
        $meanings = [];
        foreach($ar['lang'] as $k => $v) {
            if($v!='') {
                $meanings[] = [
                    'lang' => $k,
                    'translation_string' => $v
                ];
                if($k!=App::getLocale()) {
                    $presets[] = $k;
                }
            }
        }
        $translation->meanings()->createMany($meanings);
        LanguageTranslation::export();
        if(isset($ar['preset']) && $ar['preset']!='') {
            $preference = $me->preference()->firstOrCreate(['data' => json_encode(['languages_group' => $presets])]);
            $preference->ardata = ['languages_group' => [
                $ar['preset'] => $presets
            ]];
            $preference->save();
        }
        return [
            'type' => 'translation',
            'row' => $translation
        ];
    }
}
?>