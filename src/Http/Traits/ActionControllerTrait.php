<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Ry\Admin\Models\Language;
use Ry\Admin\Models\LanguageTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

trait ActionControllerTrait
{
    public function index($action=null, Request $request) {
        if(!$action)
            return $this->get_dashboard($request);
        $method = strtolower($request->getMethod());
        $translation = LanguageTranslation::whereHas('slug', function($q)use($method){
            $q->where("code", "LIKE", $method.'_%');
        })->where("translation_string", "LIKE", $action)
        ->where("lang", "=", App::getLocale())
        ->first();
        $translated_routes = [];
        if($translation) {
            $method_name = $translation->slug->code;
            $translation_id = $translation->id;
            $translated_routes = Cache::rememberForever("transroutes." . $method_name, function()use($translation_id, $action){
                $translated_routes = [];
                $site = app("centrale")->getSite();
                $trs = LanguageTranslation::whereTranslationId($translation_id)->get();
                $ar = [];
                foreach($trs as $tr) {
                    $ar[$tr->lang] = $tr->translation_string;
                }
                foreach($site->nsetup[Language::class] as $language) {
                    $translated_routes[$language['code']] = '/'.$language['code'].'/'.isset($ar[$language['code']])?$ar[$language['code']]:$action;
                }
                return $translated_routes;
            });
        }
        else {
            $method_name = $method . '_' . $action;
            $translated_routes = Cache::rememberForever("transroutes." . $method_name, function()use($action){
                $translated_routes = [];
                $site = app("centrale")->getSite();
                foreach($site->nsetup[Language::class] as $language) {
                    $translated_routes[$language['code']] = '/'.$language['code'].'/'.$action;
                }
                return $translated_routes;
            });
        }
        if($method_name!='' && method_exists($this, $method_name)) {
            $ret = $this->{$method_name}($request);
            if($ret instanceof View)
                return $ret->with("routes", $translated_routes);
            return $ret;
        }
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
}
?>