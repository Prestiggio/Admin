<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ry\Admin\Models\LanguageTranslation;
use Ry\Admin\Models\Permission;
use App, Auth;
use Ry\Admin\Models\Language;
use Ry\Admin\Models\Translation;
use Ry\Centrale\Models\SiteRestriction;

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
        $rows->map(function($item){
            $item->append("strings");
        });
        $ar = $rows->toArray();
        if($request->has("site_id")) {
            session()->put("admin_site_id", $request->get("site_id"));
            app("centrale")->setSite($request->get("site_id"));
        }
        elseif(session()->has("admin_site_id")) {
            app("centrale")->setSite(session()->get("admin_site_id"));
        }
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        $languages = [];
        foreach($setup[Language::class] as $lang) {
            $languages[] = $lang["code"];
        }
        $ar['languages'] = $languages;
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
    
    public function translatable($word, $slug=false) {
        $translation = Translation::where('code', 'LIKE', $slug?str_slug($word, '_'):$word)->first();
        if(!$translation) {
            $translation = Translation::create([
                'code' => $slug ? str_slug($word, '_') : $word
            ]);
            $translation->meanings()->updateOrCreate([
                'lang' => App::getLocale()
            ], [
                'translation_string' => $word
            ]);
        }
        return $translation;
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
    
    public function putTranslationById($translation_id, $translation_string, $lang=null) {
        if(!$lang)
            $lang = App::getLocale();
        $translation = Translation::find($translation_id);
        $translation->meanings()->updateOrCreate([
            'lang' => $lang
        ], [
            'translation_string' => $translation_string
        ]);
        return $translation;
    }
    
    public function putTranslation($slug, $translation_string, $lang=null) {
        if(!$lang)
            $lang = App::getLocale();
        $translation = Translation::firstOrCreate([
            'code' => $slug
        ]);
        $translation->meanings()->updateOrCreate([
            'lang' => $lang
        ], [
            'translation_string' => $translation_string
        ]);
        return $translation;
    }
    
    public function postTranslation($translation_string, $lang=null) {
        if(!$lang)
            $lang = App::getLocale();
        $exists = LanguageTranslation::where("translation_string", "LIKE", $translation_string)->whereLang($lang)->first();
        if($exists)
            return $exists->slug;
        $slug = str_slug($translation_string, '_', $lang);
        $translation = Translation::firstOrCreate(['code' => $slug]);
        $translation->meanings()->updateOrCreate([
            'lang' => $lang
        ], [
            'translation_string' => $translation_string
        ]);
        return $translation;
    }
    
    public function get_languages() {
        $permission = Permission::authorize(__METHOD__);
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        $languages = $setup[Language::class];
        return view("$this->theme::ldjson", [
            "theme" => "manager",
            "view" => "Admin.Languages",
            "data" => [
                "languages" => $languages
            ],
            "page" => [
                "title" => ucfirst(__("languages")),
                "href" => '/'.request()->path(),
                "icon" => "fa fa-globe-africa",
                "permission" => $permission,
                "children" => []
            ]
        ]);
    }
    
    public function post_languages(Request $request) {
        $ar = $request->all();
        if(isset($ar["languages"])) {
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            $setup[Language::class] = SiteRestriction::unescape($ar['languages']);
            $site->nsetup = $setup;
            $site->save();
        }
        
    }
}
?>