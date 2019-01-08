<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ry\Admin\Models\LanguageTranslation;

class AdminController extends Controller
{
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
    }
    
    public function index($action=null, Request $request) {
        if(!$action)
            return $this->dashboard($request);
        $action = str_replace('-', '_', $action);
        if($action!='' && method_exists($this, $action))
            return $this->$action($request);
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
    
    public function test() {
        return LanguageTranslation::writeToFiles();
    }
}