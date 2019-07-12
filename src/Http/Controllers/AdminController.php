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
use Ry\Admin\Models\Language;
use Ry\Medias\Models\Media;
use Ry\Admin\Models\Event;
use Illuminate\Filesystem\Filesystem;
use Ry\Admin\Models\Model;

class AdminController extends Controller
{
    use LanguageTranslationController;
    
    protected $viewHint = "::";
    
    protected $theme = "ryadmin";
    
    protected $me;
    
    private $perpage = 10;
    
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
        $this->me = Auth::user();
        if(app('centrale'))
            $this->perpage = app('centrale')->perpage();
    }
    
    public function get_setup() {
        $permission = Permission::authorize(__METHOD__);
        $site = app("centrale")->getSite();
        $setup = $site->nsetup;
        return view("$this->theme.ldjson", [
            "theme" => $this->theme,
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
    
    public function post_setup(Request $request) {
        $ar = $request->all();
        if(isset($ar['setup'])) {
            $site = app("centrale")->getSite();
            $setup = $site->nsetup;
            foreach($ar['setup'] as $className => $topics) {
                if(is_array($topics)) {
                    foreach($topics as $k => $v) {
                        if(is_string($k)) {
                            $setup[$className][$k] = array_filter($ar['setup'][$className][$k], function($item){
                                return $item['label']!='';
                            });
                        }
                        else {
                            $setup[$className] = $topics;
                            break;
                        }
                    }
                }
                else {
                    $setup[$className] = $topics;
                }
            }
            $site->nsetup = $setup;
            $site->save();
        }
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
    
    public function get_dashboard(Request $request) {
        return view("$this->theme{$this->viewHint}ldjson", [
            "theme" => $this->theme,
            "view" => "",
            "page" => [
                "title" => __("Tableau de bord"),
                "href" => "/"
            ]
        ]);
    }
    
    public function get_events() {
        return view("$this->theme{$this->viewHint}ldjson", [
            'theme' => $this->theme,
            'view' => 'Ry.Admin.Events',
            "data" => Event::all(),
            'page' => [
                'title' => __('Gestion des évènements'),
                'href' => '/'.__('get_events')
            ]
        ]);
    }
    
    public function get_event_models(Request $request) {
        $row = Event::find($request->get('event_id'))->append('nsetup');
        return view("$this->theme{$this->viewHint}fragment", [
            'theme' => $this->theme,
            'view' => 'Ry.Admin.Model.Check',
            'data' => Model::all(),
            'row' => $row
        ]);
    }
    
    public function post_event_models(Request $request) {
        $ar = $request->all();
        $ar = Model::unescape($ar);
        $event = Event::find($request->get('event_id'));
        $items = [];
        foreach($ar['models'] as $model_id => $checked) {
            if($checked)
                $items[] = $model_id;
        }
        $event->nsetup = [
            'models' => $items
        ];
        $event->save();
        return $items;
    }
    
    public function post_events(Request $request) {
        $event = Event::find($request->get('id'));
        if(!$event) {
            $event = new Event();
        }
        $event->code = $request->get('code');
        $event->descriptif = $request->get('descriptif');
        $event->save();
        return $event;
    }
    
    public function delete_events(Request $request) {
        Event::where('id', '=', $request->get('id'))->delete();
    }
    
    public function post_update_menus(Request $request) {
        $ar = $request->all();
        foreach($ar["layouts"] as $layout) {
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
                        
                        app('centrale')->toSite($_section, $request->get('site_id'));
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
        app("centrale")->setSite($ar['site_id']);
        $layouts = Layout::with(["sections", "roles.layoutOverrides"])->get();
        return view("$this->theme{$this->viewHint}admin.dialogs.menus", [
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
        return view("$this->theme{$this->viewHint}fragment", [
            "view" => "Ry.Admin.User",
            "subview" => "form",
            "action" => "/insert_user",
            "add_role" => $roles->count()==1 ? __("ajouter") . ' ' . __($roles->first()->name) : __("ajouter_un_utilisateur"),
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
        return view("$this->theme{$this->viewHint}fragment", array_merge([
            "view" => "Ry.Admin.User",
            "subview" => "form",
            "action" => "/update_user",
            "add_role" => $roles->count()==1 ? __("ajouter") . ' ' . __($roles->first()->name) : __("ajouter_un_utilisateur"),
            "select_roles" => $roles->get()
        ], $row->toArray()));
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
        $users = $query->paginate($this->perpage);
        $users->map(function($item){
            $item->append('nactivities');
            $item->append('thumb');
        });
        $ar = array_merge([
            'view' => 'list',
            'add_role' => $add_role
        ], $request->all());
        return view("$this->theme{$this->viewHint}ldjson", [
            "theme" => $this->theme,
            "view" => "Ry.Admin.User",
            "data" => array_merge($users->toArray(), $ar),
            "page" => [
                "title" => __("liste_des_utilisateurs"),
                "href" => "/users",
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
        
        if(env('APP_ENV')!='production') {
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
        
        event("ryadminnotify_insert_user", [$_user, [
            'user' => $_user, 
            'password' => $password]]);
        
        $_user->load("contacts");
        $_user->load("medias");
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
    
    public function post_update_user(Request $request) {
        $ar = $request->all();
        $_user = User::find($ar['id']);
        $_user->email = $ar['email'];
        $_user->name = $ar['profile']['firstname'] . ' ' . $ar['profile']['lastname'];
        $_user->save();
        
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
        
        $_user->load(["profile", "medias", "contacts", "roles"]);
        $_user->append('nactivities');
        $_user->append('thumb');
        
        app("\Ry\Profile\Http\Controllers\AdminController")->putContacts($_user, $ar['contacts']);
        
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
        foreach($setup[Language::class] as $language) {
            $contents[$language["code"]] = [
                'lang' => $language["code"],
                'bindings' => [
                    "subject" => "",
                    "signature" => ""
                ],
                'content' => Storage::disk("local")->get("mail-template.html")
            ];
        }
        $events = Event::all();
        $events->each(function($item){
            $item->append('nsetup');
            $item->makeHidden('setup');
        });
        return view("$this->theme{$this->viewHint}ldjson", [
            'theme' => $this->theme,
            'view' => 'Ry.Profile.Editor',
            'action' => '/templates_insert',
            "contents" => array_values($contents),
            "events" => Event::all(),
            "channels" => NotificationTemplate::CHANNELS,
            "presets" => [
                [
                    "title" => __("e_mail"),
                    'href' => __('get_templates'),
                    'icon' => 'fa fa-users'
                ]
            ],
            "page" => [
                "title" => __("ajouter_une_template"),
                "icon" => "fa fa-file-invoice",
                "href" => '/'.__('get_templates_add')
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
        $arevents = [];
        if(isset($ar['template']['events'])) {
            $events = array_keys($ar['template']['events']);
            foreach($events as $event) {
                $arevents[$event] = isset($ar['template']['events'][$event]['immediate']);
            }
        }
        $template->arevents = $arevents;
        if(isset($ar['template']['injections'])) {
            $template->arinjections = $ar['template']['injections'];
        }
        $template->save();
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
        $template = NotificationTemplate::find($request->get("id"));
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
        return view("$this->theme{$this->viewHint}ldjson", array_merge([
            'theme' => $this->theme,
            "view" => "Ry.Profile.Editor",
            'action' => '/templates_update',
            "contents" => array_values($contents),
            "events" => array_keys(json_decode(Storage::disk("local")->get("events.log"), true)),
            "channels" => NotificationTemplate::CHANNELS,
            "presets" => [
                [
                    "title" => __("e_mail"),
                    'href' => __('get_templates'),
                    'icon' => 'fa fa-users'
                ]
            ],
            "page" => [
                "title" => __("editer_la_template").' : '.$template->name,
                'href' => '/'. __('get_templates_edit').'?id='.$template->id,
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
        $arevents = [];
        if(isset($ar['template']['events'])) {
            $events = array_keys($ar['template']['events']);
            foreach($events as $event) {
                $arevents[$event] = isset($ar['template']['events'][$event]['immediate']);
            }
        }
        $template->arevents = $arevents;
        if(isset($ar['template']['injections'])) {
            if(!isset($ar['template']['injections']['log']))
                $ar['template']['injections']['log'] = false;
            $template->arinjections = $ar['template']['injections'];
        }
        else {
            $template->arinjections = [
                'log' => false
            ];
        }
        $template->save();
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
        Mail::to($this->me)->sendNow(new Preview($request->get('subject'), $request->get('content'), $request->get('signature'), [
            "user" => $this->me
        ]));
    }
    
    public function get_logout() {
        auth('admin')->logout();
        return redirect('/');
    }
}