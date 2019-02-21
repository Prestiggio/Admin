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
use App, Auth;
use Ry\Admin\Models\UserRole;
use Ry\Admin\Models\Role;
use Faker\Factory;
use Illuminate\Support\Facades\Mail;
use Ry\Admin\Mail\AccountCreated;
use Ry\Profile\Models\NotificationTemplate;
use Ry\Admin\Mail\Preview;
use Ry\Profile\Models\Contact;

class AdminController extends Controller
{
    use LanguageTranslationController;
    
    protected $theme = "ryadmin";
    
    protected $me;
    
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
        $this->me = Auth::user();
    }
    
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
        return [
            "type" => "setup"
        ];
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
    
    public function get_add_user(Request $request) {
        if($request->has("roles")) {
            $roles = Role::with(["permissions"])->whereIn("id", $request->get("roles"));
        }
        else {
            $roles = Role::with(["permissions"]);
        }
        return view("$this->theme::bs.add_user", [
            "row" => array_merge([
                "add_role" => $roles->count()==1 ? __("ajouter") . ' ' . __($roles->first()->name) : __("ajouter_un_utilisateur"),
                "select_roles" => $roles->get()
            ], $request->all())]);
    }
    
    public function get_edit_user($user_id, Request $request) {
        $row = User::with(["medias", "contacts", "roles"])->find($user_id)->toArray();
        if($request->has("roles")) {
            $roles = Role::with(["permissions"])->whereIn("id", $request->get("roles"));
        }
        else {
            $roles = Role::with(["permissions"]);
        }
        return view("$this->theme::bs.edit_user", [
            'row' => array_merge([
                "add_role" => $roles->count()==1 ? __("ajouter") . ' ' . __($roles->first()->name) : __("ajouter_un_utilisateur"),
                "select_roles" => $roles->get()
            ], $row)]);
    }
    
    public function get_users(Request $request) {
        $permission = Permission::authorize(__METHOD__);
        $query = User::with(["profile", "medias", "contacts", "roles"]);
        $add_role = __("ajouter_un_utilisateur");
        if($request->has("roles")) {
            $query->whereHas("roles", function($q) use ($request){
                $q->whereIn("ry_admin_roles.id", $request->get("roles"));
            });
            $_roles = Role::whereIn("id", $request->get("roles"));
            if($_roles->count()==1) {
                $add_role = __("ajouter") . ' ' . __($_roles->first()->name);
            }
        }
        elseif($request->has('guard')) {
            $query->whereIn("guard", $request->get("guard"));
        }
        else {
            $query->where("guard", "=", "manager");
        }
        $users = $query->paginate(10);
        $users->map(function($item){
            $item->setAttribute("details", app("centrale")->user_detail($item));
            return $item;
        });
        $ar = array_merge([
            'add_role' => $add_role,
            'roles' => [2]
        ], $request->all());
        return view("$this->theme::bs.users", [
            "data" => array_merge($users->toArray(), $ar),
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
        
        $faker = Factory::create(App::getLocale());
        $password = $faker->password(8);
        
        $roles = Role::whereIn("id", $request->get("roles"))->get();
        $layout = "";
        foreach ($roles as $role) {
            $layout = $role->layouts()->first()->name;
        }
        
        $_user = new User();
        $_user->guard = $layout;
        $_user->name = $user['profile']["firstname"]." ".$user['profile']["lastname"];
        $_user->email = $user["email"];
        
        $_user->password = Hash::make($password);
        $_user->save();
        
        $_user->profile()->create([
            "gender" => $user['profile']["gender"],
            "firstname" => $user['profile']["firstname"],
            "lastname" => $user['profile']["lastname"],
            "official" => $user['profile']["firstname"]." ".$user['profile']["lastname"],
            "languages" => "fr"
        ]);
        
        foreach ($roles as $role) {
            $_user->roles()->attach($role->id);
        }
        
        $_user->load("roles");
        
        if($request->hasFile('photo')) {
            $request->file('photo')->store("avatars", env('PUBLIC_DISK', 'public'));
            $path = 'avatars/'.$request->file('photo')->hashName();
            if(isset($user["photo"])) {
                $_user->medias()->create([
                    'owner_id' => $_user->id,
                    'title' => $path,
                    'path' => 'storage/'.$path,
                    'type' => 'image'
                ]);
            }
        }
        
        app("\Ry\Profile\Http\Controllers\AdminController")->putContacts($_user, $user['contacts']);
        
        event("rynotify_insert_user", [$_user, [
            'user' => $_user, 
            'password' => $password]]);
        
        $_user->load("contacts");
        $_user->load("medias");
        
        return [
            "row" => $_user,
            "type" => "users"
        ];
    }
    
    public function post_activate_user(Request $request) {
        $ar = $request->all();
        $_user = User::find($request->get('id'));
        $_user->active = $ar['active']=='true';
        $_user->save();
    }
    
    public function post_update_user(Request $request) {
        $ar = $request->all();
        $_user = User::find($ar['id']);
        $_user->email = $ar['email'];
        $_user->name = $ar['profile']['firstname'] . ' ' . $ar['profile']['lastname'];
        $_user->save();
        
        $_user->profile()->update($ar['profile']);
        
        if($request->hasFile('photo')) {
            foreach($_user->medias as $media) {
                Storage::delete($media->path);
            }
            $_user->medias()->delete();
            
            $request->file('photo')->store("avatars", env('PUBLIC_DISK', 'public'));
            $path = 'avatars/'.$request->file('photo')->hashName();
            $_user->medias()->create([
                'owner_id' => $_user->id,
                'title' => $path,
                'path' => 'storage/'.$path,
                'type' => 'image'
            ]);
        }
        
        app("\Ry\Profile\Http\Controllers\AdminController")->putContacts($_user, $ar['contacts']);
        
        return [
            "type" => "users",
            "row" => User::with(["medias", "contacts", "roles"])->find($_user->id)
        ];
    }
    
    public function delete_users(Request $request) {
        User::find($request->get('id'))->delete();
        
        Contact::whereJoinableType(User::class)->whereJoinableId($request->get("id"))->delete();
    }
    
    public function get_templates_add() {
        return view("$this->theme::templates_form", [
            'action' => '/templates_insert',
            "data" => [
                "page" => [
                    "title" => __("ajouter_une_template"),
                    "icon" => "fa fa-file-invoice"
                ],
                "content" => Storage::disk("local")->get("mail-template.html"),
                "events" => array_keys(json_decode(Storage::disk("local")->get("events.log"), true)),
                "channels" => NotificationTemplate::CHANNELS,
                "presets" => []
            ]
        ]);
    }
    
    public function post_templates_insert(Request $request) {
        $this->me = Auth::user();
        $ar = $request->all();
        $template = new NotificationTemplate();
        $template->name = $ar['template']['name'];
        $template->archannels = $ar['template']['channels'];
        $events = array_keys($ar['template']['events']);
        $arevents = [];
        foreach($events as $event) {
            $arevents[$event] = isset($ar['template']['events'][$event]['immediate']);
        }
        $template->arevents = $arevents;
        $template->arinjections = $ar['template']['injections'];
        $template->save();
        $path = "notification_templates/" . $template->id . ".html";
        Storage::disk('local')->put($path, $ar['content']);
        $template->medias()->create([
            "owner_id" => $this->me->id,
            "title" => App::getLocale(),
            "path" => $path,
        ]);
        return $template;
    }
    
    public function get_templates_edit(Request $request) {
        $template = NotificationTemplate::find($request->get("id"));
        $data = $template->toArray();
        return view("$this->theme::templates_form", [
            'action' => 'templates_update',
            "data" => array_merge([
                "page" => [
                    "title" => __("editer_la_template").' : '.$template->name,
                    "icon" => "fa fa-file-invoice"
                ],
                "content" => Storage::disk("local")->get($template->medias()->first()->path),
                "events" => array_keys(json_decode(Storage::disk("local")->get("events.log"), true)),
                "channels" => NotificationTemplate::CHANNELS,
                "presets" => []
            ], $data)
        ]);
    }
    
    public function post_templates_update(Request $request) {
        $this->me = Auth::user();
        $ar = $request->all();
        $template = NotificationTemplate::find($request->get("id"));
        $template->name = $ar['template']['name'];
        $template->archannels = $ar['template']['channels'];
        $events = array_keys($ar['template']['events']);
        $arevents = [];
        foreach($events as $event) {
            $arevents[$event] = isset($ar['template']['events'][$event]['immediate']);
        }
        $template->arevents = $arevents;
        $template->arinjections = $ar['template']['injections'];
        $template->save();
        $path = "notification_templates/" . $template->id . ".html";
        Storage::disk('local')->update($path, $ar['content']);
        return [
            "type" => "templates",
            "row" => $template
        ];
    }
    
    public function delete_templates(Request $request) {
        NotificationTemplate::find($request->get("id"))->delete();
        return [];
    }
    
    public function post_test_email(Request $request) {
        $this->me = Auth::user();
        Mail::to($this->me)->sendNow(new Preview($request->get('subject'), $request->get('content'), $request->get('signature'), [
            "user" => $this->me
        ]));
    }
    
    public function get_logout() {
        auth('admin')->logout();
        return redirect('/');
    }
}