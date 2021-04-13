<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Ry\Admin\Models\LanguageTranslation;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Illuminate\Http\Request;
use Ry\Admin\Models\Seo\CustomLayout;

class PublicController extends Controller
{   
    use ActionControllerTrait;
    
    public function translation($lang) {
        $languages = [];
        $translations = LanguageTranslation::whereLang($lang)->with(["slug"])->get();
        foreach($translations as $translation) {
            $languages[mb_convert_encoding($translation->slug->code, "UTF-8")] = $translation->translation_string;
        }
        return response()->view("ryadmin::languages", [
            "lang" => $lang,
            "translations" => json_encode($languages, JSON_UNESCAPED_UNICODE)
        ])->header('Content-Type', 'text/javascript; charset=UTF-8');
    }
    
    public function static_page($page, Request $request) {
        $blocks = CustomLayout::fetchBlocks();
        if($blocks->count()==0)
            abort(404);
        return view("ldjson", [
            "view" => "Static",
            "page" => [
                "title" => $page,
                "href" => __("/".$page)
            ]
        ]);
    }
    
    public function menu(Request $request) {
        $ar = $request->all();
        return app(AdminController::class)->copyMenus($ar['data'], $ar['site_id']);
    }
}