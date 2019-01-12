<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ry\Admin\Models\LanguageTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Ry\Admin\Http\Traits\LanguageTranslationController;
use Ry\Admin\Models\Role;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\LayoutSection;
use Ry\Admin\Models\Layout\RoleLayout;
use App\User;
use Ry\Admin\Models\Permission;

class AdminController extends Controller
{
    use LanguageTranslationController;
    
    protected $theme = "ryadmin";
    
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
    }
    
    public function index($action=null, Request $request) {
        if(!$action)
            return $this->get_dashboard($request);
        $action = str_replace('-', '_', $action);
        $action = $request->getMethod() . '_' . $action;
        if($action!='' && method_exists($this, $action))
            return $this->$action($request);
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
    
    public function post_update_menus(Request $request) {
        $ar = $request->all();
        foreach($ar["layouts"] as $layout) {
            if(isset($layout['sections'])) {
                foreach ($layout['sections'] as $section) {
                    if(isset($section['updated'])) {
                        $_section = LayoutSection::find($section['id']);
                        $_section->setup = $section["setup"];
                        $_section->save();
                    }
                }
            }
            if(isset($layout['roles'])) {
                foreach($layout['roles'] as $role) {
                    if(isset($role['updated'])) {
                        foreach($role['layout_overrides'] as $layout_override) {
                            RoleLayout::find($layout_override['id'])->update([
                                'sections_setup' => json_encode($layout_override['sections_setup'])
                            ]);
                        }
                    }
                }
            }
        }
    }
    
    public function get_menus(Request $request) {
        return $this->post_menus($request);
    }
    
    public function post_menus(Request $request) {
        $ar = $request->all();
        $layouts = Layout::with(["sections", "roles.layoutOverrides"])->get();
        return view("$this->theme::admin.dialogs.menus", [
            "navigationByRole" => [
                "page" => $ar,
                "layouts" => $layouts,
                "allowed" => LayoutSection::where("default_setup", "LIKE", "%".$ar['permission']."%")->pluck('layout_id')
            ],
            "page" => $ar
        ]);
    }
    
    public function post_add_user(Request $request) {
        return view("ryadmin::bs.add_user");
    }
    
    public function get_users(Request $request) {
        $permission = Permission::authorize($this);
        $users = User::with(["medias", "contacts", "roles"])->where("guard", "=", "manager")->paginate(10);
        return view("ryadmin::bs.users", [
            "users" => $users,
            "page" => [
                "title" => "Liste des utilisateurs",
                "href" => "/users",
                "permission" => $permission,
                "icon" => "fa fa-users"
            ]
        ]);
    }
    
    public function post_insert_user(Request $request) {
        $user = $request->all();
        
        $_user = new User();
        $_user->guard = "manager";
        $_user->name = $user['profile']["firstname"]." ".$user['profile']["lastname"];
        $_user->email = $user["email"];
        $_user->password = Hash::make($user['password']);
        $_user->save();
        
        $_user->profile()->create([
            "gender" => $user['profile']["gender"],
            "firstname" => $user['profile']["firstname"],
            "lastname" => $user['profile']["lastname"],
            "official" => $user['profile']["firstname"]." ".$user['profile']["lastname"],
            "languages" => "fr"
        ]);
        
        $request->file('photo')->storeAs($path, $name)
        
        if(isset($user["photo"])) {
            $_user->medias()->create([
                'owner_id' => $_user->id,
                'title' => $user['photo'],
                'path' => $user['photo'],
                'type' => 'image'
            ]);
        }
        
        app("\Ry\Profile\Http\Controllers\AdminController")->putContacts($_user, $user['contacts']);
        
        return [
            "all" => $request->all(),
            "files" => $request->file('photo')
        ];
    }
    
    public function get_logout() {
        auth('admin')->logout();
        return redirect('/');
    }
}