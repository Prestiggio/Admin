<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ry\Admin\Models\LanguageTranslation;
use Ry\Admin\Models\Permission;
use App, Auth;
use Ry\Admin\Models\Language;
use Ry\Admin\Models\Translation;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;

trait LanguageTranslationController
{
    use HasJsonSetup;
    
    public function get_translations(Request $request) {
        $perPage = 10;
        if($request->has("s") && $request->get('s')!='') {
            $translation_query = Translation::whereHas('meanings', function($q)use($request){
                $q->where('translation_string', 'LIKE', '%'.$request->get('s').'%');
            })->orWhere('code', 'LIKE', '%'.$request->get('s').'%')->orderBy('code');
        }
        else {
            $translation_query = Translation::orderBy('code');
        }
        $rows = $translation_query->paginate($perPage);
        $rows->map(function($item){
            $item->append("strings");
        });
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
        if(isset($setup[Language::class])) {
            foreach($setup[Language::class] as $lang) {
                $languages[] = $lang["code"];
            }
        }
        $permission = Permission::authorize(__METHOD__);
        return view("$this->theme{$this->viewHint}ldjson", [
            "theme" => $this->theme,
            "view" => "Ry.Admin.Translator",
            "data" => $rows,
            "languages" => $languages,
            "page" => [
                "title" => ucfirst(__("Traductions")),
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
        return view("$this->theme{$this->viewHint}fragment", [
            "theme" => $this->theme,
            "view" => "Ry.Admin.Traductions",
            "presets" => $presets, 
            "locale" => App::getLocale()
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
        if(!isset($ar['lang'][config('app.fallback_locale')])) {
            $lg0 = '';
            foreach($ar['lang'] as $k => $v) {
                $lg0 = $v;
                if($v!='')
                    break;
            }
            $ar['lang'][config('app.fallback_locale')] = $lg0;
        }
        if($ar['lang'][config('app.fallback_locale')]=='')
            abort(404, __("Aucune traduction n'a été soumise"));
        if(Translation::where("code", "LIKE", $ar['lang'][config('app.fallback_locale')])->exists())
            return abort(409, __("Cette traduction existe déja"));
        
        $translation = Translation::create([
            'code' => $ar['lang'][config('app.fallback_locale')]
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
            $lang = config('app.fallback_locale');
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
            $lang = config('app.fallback_locale');
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
            $lang = config('app.fallback_locale');
        $exists = LanguageTranslation::where("translation_string", "LIKE", $translation_string)->whereLang($lang)->first();
        if($exists)
            return $exists->slug;
        $slug = $translation_string;
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
        $languages = isset($setup[Language::class]) ? $setup[Language::class] : [];
        return view("$this->theme{$this->viewHint}ldjson", [
            "theme" => $this->theme,
            "view" => "Ry.Admin.Languages",
            "data" => [
                "languages" => $languages
            ],
            "page" => [
                "title" => ucfirst(__("Langues")),
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
            $setup[Language::class] = static::unescape($ar['languages']);
            $site->nsetup = $setup;
            $site->save();
        }
    }
    
    public function get_gettext() {
        $contents = [];
        $translations = Translation::all();
        foreach($translations as $translation) {
            $contents[] = '__("'.$translation->code.'")';
        }
        Storage::disk('local')->put('translations.php', "<?php\n" . implode(";\n",$contents) . "\n?>");
    }
    
    public function pojson() {
        $fs = new Filesystem();
        $files = $fs->glob(base_path("*-gettext.json"));
        foreach($files as $file) {
            preg_match("/(\w+)-gettext\.json$/", $file, $match);
            $lang = $match[1];
            $raw = $fs->get($file);
            $ar = json_decode($raw, true);
            foreach($ar as $code => $v) {
                if($code == '')
                    continue;
                list($null, $translation) = $v;
                if($translation=='')
                    continue;
                $_translation = Translation::firstOrCreate(['code' => $code]);
                $_translation->meanings()->updateOrCreate([
                    'lang' => $lang
                ], [
                    'translation_string' => $translation
                ]);
            }
        }
        LanguageTranslation::export();
    }
}
?>