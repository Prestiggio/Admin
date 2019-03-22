<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Ry\Admin\Models\LanguageTranslation;

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
        if($translation)
            $action = $translation->slug->code;
        else
            $action = $method . '_' . $action;
        if($action!='' && method_exists($this, $action))
            return $this->$action($request);
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
}
?>