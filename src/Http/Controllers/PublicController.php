<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ry\Admin\Http\Traits\LanguageTranslationController;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\LayoutSection;
use Ry\Admin\Models\Layout\RoleLayout;
use App\User;
use Ry\Admin\Models\Permission;
use Illuminate\Support\Facades\Storage;
use Ry\Admin\Models\LanguageTranslation;
use App;
use Ry\Admin\Models\UserRole;
use Ry\Admin\Models\Role;
use Faker\Factory;
use Illuminate\Support\Facades\Mail;
use Ry\Admin\Mail\AccountCreated;

class PublicController extends Controller
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
        if($translation)
            $action = $translation->slug->code;
        else
            $action = $method . '_' . $action;
        if($action!='' && method_exists($this, $action))
            return $this->$action($request);
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
    
    public function translation($lang) {
        $languages = [];
        $translations = LanguageTranslation::whereLang($lang)->with(["slug"])->get();
        foreach($translations as $translation) {
            $languages[$translation->slug->code] = $translation->translation_string;
        }
        return response()->view("ryadmin::languages", [
            "lang" => $lang,
            "translations" => json_encode($languages)
        ])->header('Content-Type', 'text/javascript; charset=UTF-8');
    }
}