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
use Ry\Profile\Models\NotificationTemplate;
use Ry\Admin\Mail\Preview;
use Ry\Profile\Models\Contact;
use Ry\Admin\Models\Language;
use Ry\Medias\Models\Media;
use Illuminate\Filesystem\Filesystem;
use Ry\Admin\Models\Model;
use Ry\Admin\Models\Alert;
use Ry\Centrale\SiteScope;
use Ry\Centrale\Models\SiteRestriction;
use Ry\Profile\Models\Profile;
use Ry\Geo\Http\Controllers\PublicController as GeoController;

class AdminController extends Controller
{
    use LanguageTranslationController;
    
    protected $me;
    
    private $perpage = 10;
    
    protected $force_password = false;
    
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
        $this->me = Auth::user();
        if(app('centrale'))
            $this->perpage = app('centrale')->perpage();
    }
    
    public function get_setup(Request $request) {
        $permission = Permission::authorize(__METHOD__);
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        return view("ldjson", [
            "view" => "App.Manager.Setup",
            "data" => $setup,
            "page" => [
                "title" => __("Setup"),
                "href" => "/setup",
                "icon" => "fa fa-gear",
                "permission" => $permission
            ]
        ]);
    }
    
    public function get_setup_tree() {
        $permission = Permission::authorize(__METHOD__);
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        return view("ldjson", [
            "view" => "Ry.Admin.SetupTree",
            "data" => $setup,
            "page" => [
                "title" => __("Setup"),
                "href" => "/setup",
                "icon" => "fa fa-gear",
                "permission" => $permission
            ]
        ]);
    }
    
    public function post_setup_tree(Request $request) {
        $ar = json_decode($request->get('setup'), true);
        $site = app("centrale")->getSite();
        $site->nsetup = $ar;
        $site->save();
    }
    
    public function post_setup(Request $request) {
        $ar = $request->all();
        if(isset($ar['setup'])) {
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            foreach($ar['setup'] as $k => $v) {
                $setup[$k] = $v;
            }
            $site->nsetup = $setup;
            $site->save();
        }
    }
    
    public function get_hash(Request $request) {
        return Hash::make($request->get('password'));
    }
    
    public function post_upload(Request $request) {
        if($request->hasFile("logo")) {
            $request->file('logo')->store('setup', env('PUBLIC_DISK', 'public'));
            $path = 'storage/setup/' . $request->file('logo')->hashName();
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            $setup['logo'] = $path;
            $site->nsetup = $setup;
            $site->save();
        }
        if($request->hasFile("nophoto")) {
            $request->file('nophoto')->store('setup', env('PUBLIC_DISK', 'public'));
            $path = 'storage/setup/' . $request->file('nophoto')->hashName();
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            $setup['nophoto'] = $path;
            $site->nsetup = $setup;
            $site->save();
        }
        if($request->hasFile("favicon")) {
            $request->file('favicon')->store('setup', env('PUBLIC_DISK', 'public'));
            $path = 'storage/setup/' . $request->file('favicon')->hashName();
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            $setup['favicon'] = $path;
            $site->nsetup = $setup;
            $site->save();
        }
    }
    
    public function index($action=null, Request $request) {
        Auth::user()->log($action);
        if(!$action)
            return $this->get_dashboard($request);
        $method = strtolower($request->getMethod());
        $controller_action = $method . '_' . $action;
        if(method_exists($this, $controller_action)) {
            return $this->$controller_action($request);
        }
        else {
            $query = LanguageTranslation::where('translation_string', 'LIKE', '/'.$action.'%')->whereHas('slug', function($q){
                $q->where("code", "LIKE", "/%");
            });
            if($query->exists()) {
                $translations = $query->get();
                foreach($translations as $translation) {
                    $controller_action = $method . '_' . preg_replace('/^\//', '', $translation->slug->code);
                    if(method_exists($this, $controller_action))
                        return $this->$controller_action($request);
                }
            }
        }
        abort(404);
    }
    
    public function get_dashboard(Request $request) {
        return view("ldjson", [
            "view" => "",
            "page" => [
                "title" => __("Tableau de bord"),
                "href" => "/"
            ]
        ]);
    }
    
    public function get_events() {
        return view("ldjson", [
            'view' => 'Ry.Admin.Events',
            "data" => Alert::all(),
            'page' => [
                'title' => __('Gestion des alertes'),
                'href' => __('/events')
            ]
        ]);
    }
    
    public function get_event_add() {
        return view("fragment", [
            'view' => 'Ry.Admin.Alert.Form',
            'page' => [
                'title' => __('Gestion des alertes'),
                'href' => __('/event_add')
            ]
        ]);
    }
    
    public function get_event_edit(Request $request) {
        $row = Alert::find($request->get('id'));
        return view("fragment", [
            'view' => 'Ry.Admin.Alert.Form',
            "row" => $row,
            'page' => [
                'title' => __('Gestion des alertes'),
                'href' => __('/event_add')
            ]
        ]);
    }
    
    public function get_event_models(Request $request) {
        $row = Alert::find($request->get('event_id'))->append('nsetup');
        return view("fragment", [
            'view' => 'Ry.Admin.Model.Check',
            'row' => $row
        ]);
    }
    
    public function post_events(Request $request) {
        $event = Alert::find($request->get('id'));
        if(!$event) {
            $event = new Alert();
        }
        $event->code = $request->get('code');
        $event->descriptif = $request->get('descriptif');
        $event->nsetup = $request->get('nsetup');
        $event->save();
        $alerts = Alert::all();
        return [
            'type' => 'alerts',
            'data' => $alerts
        ];
    }
    
    public function delete_events(Request $request) {
        Alert::where('id', '=', $request->get('id'))->delete();
    }
    
    public function copyMenus($layouts, $site_id) {
        $debug = [];
        foreach($layouts as $layout) {
            if(isset($layout['sections'])) {
                foreach ($layout['sections'] as $section) {
                    $_section = LayoutSection::whereName($section['name'])->whereLayoutId($section['layout_id'])->first();
                    $debug[] = $_section;
                    if(!$_section) {
                        $_section = new LayoutSection();
                        $_section->layout_id = $section['layout_id'];
                        $_section->name = $section['name'];
                        $_section->active = ($section['active']=='true');
                    }
                    $_section->setup = $section["setup"];
                    $_section->save();
                    
                    app('centrale')->toSite($_section, $site_id);
                }
            }
            if(isset($layout['roles'])) {
                foreach($layout['roles'] as $role) {
                    foreach($role['layout_overrides'] as $layout_override) {
                        RoleLayout::whereRoleId($layout_override['role_id'])->whereLayoutId($layout_override['layout_id'])->update([
                            'sections_setup' => json_encode($layout_override['setup'])
                        ]);
                    }
                }
            }
        }
        return $debug;
    }
    
    public function copyMails($site_source_id, $site_destination_id) {
        $destination_template_restrictions = SiteRestriction::whereScopeType(NotificationTemplate::class)->whereSiteId($site_destination_id)->get();
        $alerted = [];
        foreach ($destination_template_restrictions as $destination_template_restriction) {
            $destination_scope = $destination_template_restriction->scope()->withoutGlobalScope(app(SiteScope::class))->first();
            if($destination_scope) {
                foreach($destination_scope->alerts as $alert) {
                    $alerted[$alert->code] = true;
                }
            }
        }
        $latest_site_template_restrictions = SiteRestriction::whereScopeType(NotificationTemplate::class)->whereSiteId($site_source_id)->get();
        foreach($latest_site_template_restrictions as $latest_site_template_restriction) {
            $scope = $latest_site_template_restriction->scope()->withoutGlobalScope(app(SiteScope::class))->first();
            $_alerted = false;
            if($scope) {
                foreach($scope->alerts as $alert) {
                    if(isset($alerted[$alert->code])) {
                        $_alerted = true;
                        break;
                    }
                }
                if($_alerted)
                    continue;
                
                $template = $scope->replicate();
                $template->save();
                foreach($scope->alerts as $alert) {
                    $template->alerts()->attach($alert->id);
                }
                app("centrale")->toSite($template, $site_destination_id);
                $medias = Media::whereMediableType(NotificationTemplate::class)->whereMediableId($scope->id)->get();
                foreach($medias as $prev_media) {
                    $media = $prev_media->replicate();
                    $path = "notification_templates/" . $template->id . "-".$media->title.".html";
                    if(!Storage::disk('local')->exists($path) && Storage::disk('local')->copy($media->path, $path)) {
                        $media->path = $path;
                    }
                    $media->mediable_id = $template->id;
                    $media->save();
                }
            }
        }
    }
    
    public function updateMenus($layouts, $site_id) {
        foreach($layouts as $layout) {
            if(isset($layout['sections'])) {
                foreach ($layout['sections'] as $section) {
                    if(isset($section['updated'])) {
                        if($section['id']>0) {
                            $_section = LayoutSection::find($section['id']);
                        }
                        else {
                            $_section = new LayoutSection();
                            $_section->layout_id = $section['layout_id'];
                            $_section->name = $section['name'];
                            $_section->active = ($section['active']=='true');
                        }
                        $_section->setup = $section["setup"];
                        $_section->save();
                        
                        app('centrale')->toSite($_section, $site_id);
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
    
    public function post_update_menus(Request $request) {
        $ar = $request->all();
        if($request->has('site_id'))
            app('centrale')->setSite($request->get('site_id'));
        $site_id = app("centrale")->getSite()->id;
        $this->updateMenus($ar['layouts'], $site_id);
        return [
            "type" => "setup"
        ];
    }
    
    public function get_menus(Request $request) {
        return $this->post_menus($request);
    }
    
    public function post_menus(Request $request) {
        $ar = $request->all();
        app("centrale")->setSite($ar['site_id']);
        $layouts = Layout::with(["sections", "roles.layoutOverrides"])->get();
        return view("ryadmin::dialogs.menus", [
            "site_id" => $request->get('site_id'),
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
        return view("fragment", [
            "view" => "Ry.Admin.User",
            "subview" => "form",
            "action" => "/insert_user",
            "add_role" => $roles->count()==1 ? __("Ajouter") . ' ' . __($roles->first()->name) : __("Ajouter un utilisateur"),
            "select_roles" => $roles->get()
        ]);
    }
    
    public function get_edit_user($user_id, Request $request) {
        $row = User::with(["profile", "medias", "contacts", "roles"])->find($user_id);
        $row->append('nactivities');
        $row->append('thumb');
        if($request->has("roles")) {
            $roles = Role::with(["permissions"])->whereIn("id", $request->get("roles"));
        }
        else {
            $roles = Role::with(["permissions"]);
        }
        return view("fragment", array_merge([
            "view" => "Ry.Admin.User",
            "subview" => "form",
            "action" => "/update_user",
            "add_role" => $roles->count()==1 ? __("Ajouter") . ' ' . __($roles->first()->name) : __("Ajouter un utilisateur"),
            "select_roles" => $roles->get()
        ], $row->toArray()));
    }
    
    public function get_users(Request $request) {
        $permission = Permission::authorize(__METHOD__);
        $query = User::with(["profile", "medias", "contacts", "roles"]);
        $add_role = __("Ajouter un utilisateur");
        if($request->has("roles")) {
            $query->whereHas("roles", function($q) use ($request){
                $q->whereIn("ry_admin_roles.id", $request->get("roles"));
            });
            $_roles = Role::whereIn("id", $request->get("roles"));
            if($_roles->count()==1) {
                $add_role = __("Ajouter") . ' ' . __($_roles->first()->name);
            }
        }
        elseif($request->has('guard')) {
            $query->whereIn("guard", $request->get("guard"));
        }
        else {
            $query->where("guard", "=", "manager");
        }
        $users = $query->paginate($this->perpage);
        $users->map(function($item){
            $item->append('nactivities');
            $item->append('thumb');
        });
        $ar = array_merge([
            'view' => 'list',
            'add_role' => $add_role
        ], $request->all());
        return view("ldjson", [
            "view" => "Ry.Admin.User",
            "data" => array_merge($users->toArray(), $ar),
            "page" => [
                "title" => __("Liste des utilisateurs"),
                "href" => __("/users"),
                "permission" => $permission,
                "icon" => "fa fa-users"
            ]
        ]);
    }
    
    public function models() {
        $filesystem = new Filesystem();
        $ar = $filesystem->allFiles(dirname(dirname(dirname(dirname(__DIR__)))));
        foreach($ar  as $a) {
            if(preg_match('/models/i', $a->getPath()) 
                && !preg_match('/trait/i', $a->getPath())
                && !preg_match('/ry\/creno/i', $a->getPath())
                && !preg_match('/ry\/macentrale/i', $a->getPath())
                && !preg_match('/ry\/md/i', $a->getPath())) {
                $txt = $a->getContents();
                
                if(preg_match('/interface /', $txt))
                    continue;
                
                preg_match('/namespace ([\w\\\\]+)/i', $txt, $ns);
                preg_match('/class (\w+)/', $txt, $cl);
                $rr = (isset($ns[1])?$ns[1]:'') . '\\' . (isset($cl[1])?$cl[1]:'');
                $m = Model::where('qualified_name', '=', $rr)->first();
                if(!$m)
                    $m = new Model();
                $m->qualified_name = $rr;
                $m->path = $a->getPath();
                $d = [];
                try {
                    $d = $rr::first();
                }
                catch(\Exception $e) {
                    
                }
                $m->nsetup = $d ? json_decode($d->toJson(), true) : [];
                $m->save();
            }
        }
    }
    
    public function post_insert_user(Request $request) {
        $user = $request->all();
        
        if(User::whereEmail($user['email'])->exists())
            abort(410, __("L'adresse email est déjà utilisé"));
        
        if($this->force_password && $request->has('password') && $request->get('password')!='')
            $password = $request->get('password');
        elseif(env('APP_ENV')!='production') {
            $password = 'admin12345';
        }
        else {
            $faker = Factory::create(App::getLocale());
            $password = $faker->password(8);
        }
        
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
        
        $setup = null;
        if(isset($user['profile']['nsetup'])) {
            $nsetup = $user['profile']['nsetup'];
            Profile::unescape($nsetup);
            $setup = json_encode($nsetup);
        }
        if(isset($user['profile']['adresse'])) {
            $user['profile']['adresse_id'] = app(GeoController::class)->generate($user['profile']['adresse'])->id;
            unset($user['profile']['adresse']);
        }
        
        $_user->profile()->create([
            "gender" => $user['profile']["gender"],
            "firstname" => $user['profile']["firstname"],
            "lastname" => $user['profile']["lastname"],
            "official" => $user['profile']["firstname"]." ".$user['profile']["lastname"],
            "adresse_id" => isset($user['profile']['adresse_id']) ? $user['profile']['adresse_id'] : null,
            "languages" => "fr",
            "setup" => $setup
        ]);
        
        foreach ($roles as $role) {
            if(!$_user->roles()->whereRoleId($role->id)->exists())
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
        
        event("ryadminnotify_insert_" . $_user->guard, [$_user, [
            'user' => $_user, 
            'password' => $password]]);
        
        $_user->load(["contacts", "medias"]);
        $_user->append('nactivities');
        $_user->append('thumb');
        
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
    
    public function post_update_password(Request $request) {
        $ar = $request->all();
        $_user = auth()->user();
        $_user->refresh();
        if($ar['password']!=$ar['password_confirmation']) {
            return (object)[
                'status' => 'error',
                'message' => __('Les mots de passe sont différents')
            ];
        }
        else {
            if(Hash::check($ar['password_old'], $_user->password)) {
                $_user->password = Hash::make($ar['password']);
            }
            else {
                return (object)[
                    'status' => 'error',
                    'message' => __("L'ancien mot de passe n'est pas valide."),
                    'type' => 'users'
                ];
            }
        }
        $_user->save();
        return $_user;
    }
    
    public function post_update_me(Request $request) {
        $ar = $request->all();
        $_user = auth()->user();
        $_user->email = $ar['email'];
        if($this->force_password && isset($ar['password']) && $ar['password']!='') {
            $_user->password = Hash::make($ar['password']);
        }
        else{
            if($ar['password']!='') {
                if($ar['password']!=$ar['password_confirmation']) {
                    return [
                        'status' => 'error',
                        'message' => __('Les mots de passe sont différents')
                    ];
                }
                else {
                    if(Hash::check($ar['password_old'], $_user->password)) {
                        $_user->password = Hash::make($ar['password']);
                    }
                    else {
                        return [
                            'status' => 'error',
                            'message' => __("L'ancien mot de passe n'est pas valide."),
                            'type' => 'users'
                        ];
                    }
                }
            }
        }
        $_user->name = $ar['profile']['firstname'] . ' ' . $ar['profile']['lastname'];
        if(isset($ar['active'])) {
            $_user->active = $ar['active'];
        }
        $_user->save();
        
        if(isset($ar['profile']['nsetup'])) {
            $nsetup = $ar['profile']['nsetup'];
            Profile::unescape($nsetup);
            $setup = json_encode($nsetup);
            $ar['profile']['setup'] = $setup;
            unset($ar['profile']['nsetup']);
        }
        if(isset($ar['profile']['adresse'])) {
            $ar['profile']['adresse_id'] = app(GeoController::class)->generate($ar['profile']['adresse'])->id;
            unset($ar['profile']['adresse']);
        }
        $_user->profile()->update($ar['profile']);
        
        if($request->has("nophoto")) {
            foreach($_user->medias as $media) {
                Storage::delete($media->path);
            }
            $_user->medias()->delete();
        }
        elseif($request->hasFile('photo')) {
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
        
        $_user->load(["profile", "medias", "contacts", "roles"]);
        $_user->append('nactivities');
        $_user->append('thumb');
        
        return [
            "type" => "users",
            "row" => $_user
        ];
    }
    
    public function post_update_user(Request $request) {
        $ar = $request->all();
        $_user = User::find($ar['id']);
        $_user->email = $ar['email'];
        if($this->force_password && isset($ar['password']) && $ar['password']!='') {
            $_user->password = Hash::make($ar['password']);
        }
        $_user->name = $ar['profile']['firstname'] . ' ' . $ar['profile']['lastname'];
        if(isset($ar['active'])) {
            $_user->active = $ar['active'];
        }
        $_user->save();
        
        if(isset($ar['profile']['nsetup'])) {
            $nsetup = $ar['profile']['nsetup'];
            Profile::unescape($nsetup);
            $setup = json_encode($nsetup);
            $ar['profile']['setup'] = $setup;
            unset($ar['profile']['nsetup']);
        }
        if(isset($ar['profile']['adresse'])) {
            $ar['profile']['adresse_id'] = app(GeoController::class)->generate($ar['profile']['adresse'])->id;
            unset($ar['profile']['adresse']);
        }
        if(!isset($ar['profile']['official']) && isset($ar['profile']['firstname']) && isset($ar['profile']['lastname']))
            $ar['profile']['official'] = $ar['profile']['firstname'].' '.$ar['profile']['lastname'];
        $_user->profile()->update($ar['profile']);
        
        if($request->has("nophoto")) {
            foreach($_user->medias as $media) {
                Storage::delete($media->path);
            }
            $_user->medias()->delete();
        }
        elseif($request->hasFile('photo')) {
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
        
        $_user->load(["profile", "medias", "contacts", "roles"]);
        $_user->append('nactivities');
        $_user->append('thumb');
        
        return [
            "type" => "users",
            "row" => $_user
        ];
    }
    
    public function delete_users(Request $request) {
        User::find($request->get('id'))->delete();
        
        Contact::whereJoinableType(User::class)->whereJoinableId($request->get("id"))->delete();
    }
    
    public function get_templates_add() {
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        $contents = [];
        $fs = new Filesystem();
        foreach($setup[Language::class] as $language) {
            $contents[$language["code"]] = [
                'lang' => $language["code"],
                'bindings' => [
                    "subject" => "",
                    "signature" => ""
                ],
                'content' => Storage::disk("local")->exists("mail-template.html") ? Storage::disk("local")->get("mail-template.html") : $fs->get(__DIR__ . '/../../assets/mail-template.html')
            ];
        }
        $alerts = Alert::all();
        $alerts->each(function($item){
            $item->append('nsetup');
            $item->makeHidden('setup');
        });
        return view("ldjson", [
            'view' => 'Ry.Profile.Editor',
            'action' => '/templates_insert',
            "contents" => array_values($contents),
            "all_alerts" => $alerts,
            "channels" => NotificationTemplate::CHANNELS,
            "presets" => [
                [
                    "title" => __("E-mail"),
                    'href' => __('/templates'),
                    'icon' => 'fa fa-users'
                ]
            ],
            'parents' => [
                [
                    'href' => '/templates',
                    "title" => __("E-mail"),
                ]
            ],
            "page" => [
                "title" => __("Ajouter une template"),
                "icon" => "fa fa-file-invoice",
                "href" => __('/templates_add')
            ],
            "ckeditor" => [
                "modules" => ["ry"]
            ]
        ]);
    }
    
    public function post_templates_insert(Request $request) {
        $this->me = Auth::user();
        $ar = $request->all();
        $template = new NotificationTemplate();
        $template->name = $ar['template']['name'];
        $template->archannels = isset($ar['template']['channels']) ? $ar['template']['channels'] : [];
        $template->nsetup = $ar['template']['nsetup'];
        $template->save();
        if(isset($ar['alerts'])) {
            foreach($ar['alerts'] as $alert_id => $alert) {
                if($alert==0) {
                    $template->alerts()->detach($alert_id);
                }
                elseif(!$template->alerts()->whereAlertId($alert_id)->exists()) {
                    $template->alerts()->attach($alert_id);
                }
            }
        }
        foreach($ar['contents'] as $content) {
            $path = "notification_templates/" . $template->id . "-".$content["lang"].".html";
            Storage::disk('local')->put($path, $content["content"]);
            $template->medias()->create([
                "owner_id" => $this->me->id,
                "title" => $content["lang"],
                "descriptif" => isset($content['bindings']) ? json_encode($content['bindings']) : '',
                "path" => $path,
            ]);
        }
        $template->append('nsetup');
        $template->setAttribute('title', $template->name);
        $template->setAttribute('subject', isset($content['bindings']['subject'])?$content['bindings']['subject']:$template->name);
        $template->makeVisible(['title', 'subject']);
        return redirect('/templates_edit?id='.$template->id)->with('message', [
            'class' => 'alert-success',
            'content' => __('La template <a href=":link">:template</a> a été ajouté avec succès', [
                'template' => $template->name,
                'link' => '/templates_edit?id='.$template->id
            ])
        ]);
    }
    
    public function get_templates_edit(Request $request) {
        $template = NotificationTemplate::with("alerts")->find($request->get("id"));
        $template->makeHidden('channels');
        $template->append('nsetup');
        $contents = [];
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        foreach($setup[Language::class] as $language) {
            $contents[$language["code"]] = [
                'lang' => $language["code"],
                'bindings' => [
                    "subject" => "",
                    "signature" => ""
                ],
                'content' => ""
            ];
        }
        foreach($template->medias as $file) {
            $contents[$file->title] = [
                'id' => $file->id,
                'lang' => $file->title,
                'bindings' => $file->descriptif!='' ? json_decode($file->descriptif) : [],
                'content' => Storage::disk("local")->get($file->path)
            ];
        }
        $data = $template->toArray();
        $alert_ids = $template->alerts->pluck('id', null);
        $alerts = Alert::all();
        $alerts->each(function($item)use($alert_ids){
            $item->append('nsetup');
            $item->makeHidden('setup');
            $item->setAttribute('selected', in_array($item->id, $alert_ids->toArray()));
        });
        return view("ldjson", array_merge([
            "view" => "Ry.Profile.Editor",
            'action' => '/templates_update',
            "contents" => array_values($contents),
            "all_alerts" => $alerts,
            "channels" => NotificationTemplate::CHANNELS,
            "presets" => [
                [
                    "title" => __("E-mail"),
                    'href' => __('/templates'),
                    'icon' => 'fa fa-users'
                ]
            ],
            'parents' => [
                [
                    'href' => __('/templates'),
                    "title" => __("E-mail"),
                ]
            ],
            "page" => [
                "title" => __("Editer la template").' : '.$template->name,
                'href' => __('/templates_edit').'?id='.$template->id,
                "icon" => "fa fa-file-invoice"
            ],
            "ckeditor" => [
                "modules" => ["ry"]
            ]
        ], $data));
    }
    
    public function post_templates_update(Request $request) {
        $this->me = Auth::user();
        $ar = $request->all();
        $template = NotificationTemplate::find($request->get("id"));
        $template->name = $ar['template']['name'];
        $template->archannels = isset($ar['template']['channels']) ? $ar['template']['channels'] : [];
        $template->nsetup = $ar['template']['nsetup'];
        $template->save();
        if(isset($ar['alerts'])) {
            foreach($ar['alerts'] as $alert_id => $alert) {
                if($alert==0) {
                    $template->alerts()->detach($alert_id);
                }
                elseif(!$template->alerts()->whereAlertId($alert_id)->exists()) {
                    $template->alerts()->attach($alert_id);
                }
            }
        }
        foreach($ar['contents'] as $content) {
            $path = "notification_templates/" . $template->id . "-".$content["lang"].".html";
            if($content['id']!="") {
                $media = Media::find($content['id']);
                $media->descriptif = json_encode($content['bindings']);
                $media->save();
                Storage::disk('local')->update($path, $content["content"]);
            }
            elseif($content["content"]!="" && $content["bindings"]["subject"]!="") {
                Storage::disk('local')->put($path, $content["content"]);
                $template->medias()->create([
                    "owner_id" => $this->me->id,
                    "title" => $content["lang"],
                    "descriptif" => isset($content['bindings']) ? json_encode($content['bindings']) : '',
                    "path" => $path,
                ]);
            }
        }
        $template->append('nsetup');
        $template->setAttribute('title', $template->name);
        $template->setAttribute('subject', isset($content['bindings']['subject'])?$content['bindings']['subject']:$template->name);
        $template->makeVisible(['title', 'subject']);
        return redirect('/templates_edit?id='.$template->id)->with('message', [
            'class' => 'alert-success',
            'content' => __('La template <a href=":link">:template</a> a été modifié avec succès', [
                'template' => $template->name,
                'link' => '/templates_edit?id='.$template->id
            ])
        ]);
    }
    
    public function delete_templates(Request $request) {
        NotificationTemplate::find($request->get("id"))->delete();
        return [];
    }
    
    public function post_test_email(Request $request) {
        $this->me = Auth::user();
        Mail::to($this->me)->sendNow(new Preview($request->get('subject'), $request->get('content'), $request->get('signature'), $request->all()));
    }
    
    public function get_logout() {
        auth('admin')->logout();
        return redirect('/');
    }
}