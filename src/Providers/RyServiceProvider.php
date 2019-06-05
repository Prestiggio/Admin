<?php

namespace Ry\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Ry\Admin\Console\Commands\Admin;
use Ry\Admin\Console\Commands\UserZero;
use Ry\Admin\Http\Middleware\Administration;
use Ry\Admin\Http\View\Composers\AuthComposer;
use Illuminate\Support\Facades\Gate;
use Ry\Admin\Models\Permission;
use App\User;
use Illuminate\Support\Facades\Cache;
use Ry\Admin\Console\Commands\RegisterLayoutSection;
use Ry\Admin\Console\Commands\Ability;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Ry\Admin\Mail\EventCaught;
use Ry\Admin\Mail\UserInsertCaught;
use Ry\Profile\Models\NotificationTemplate;
use Ry\Admin\Console\Commands\RegisterEvent;
use Ry\Admin\RyAdmin;
use Illuminate\Foundation\Http\Events\RequestHandled;

class RyServiceProvider extends ServiceProvider
{
	/**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	/*
    	$this->publishes([    			
    			__DIR__.'/../config/ryadmin.php' => config_path('ryadmin.php')
    	], "config");  
    	$this->mergeConfigFrom(
	        	__DIR__.'/../config/ryadmin.php', 'ryadmin'
	    );
    	$this->publishes([
    			__DIR__.'/../assets' => public_path('vendor/ryadmin'),
    	], "public");    	
    	*/
    	//ressources
    	$this->loadViewsFrom(__DIR__.'/../ressources/views', 'ryadmin');
    	$this->loadTranslationsFrom(__DIR__.'/../ressources/lang', 'ryadmin');
    	/*
    	$this->publishes([
    			__DIR__.'/../ressources/views' => resource_path('views/vendor/ryadmin'),
    			__DIR__.'/../ressources/lang' => resource_path('lang/vendor/ryadmin'),
    	], "ressources");
    	*/
    	$this->publishes([
    			__DIR__.'/../database/factories/' => database_path('factories'),
	        	__DIR__.'/../database/migrations/' => database_path('migrations')
	    ], 'migrations');
    	$this->map();
    	//$kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
    	//$kernel->pushMiddleware('Ry\Facebook\Http\Middleware\Facebook');
    	
    	if(Schema::hasTable('ry_admin_permissions')) {
    	    $permissions = Cache::get('ryadmin.permissions');
    	    
    	    if(!$permissions) {
    	        $permissions = Permission::pluck("name");
    	        Cache::put('ryadmin.permissions', $permissions->toArray(), 60*48);
    	    }
    	    else {
    	        $permissions = collect($permissions);
    	    }
    	    
    	    $permissions->each(function($permission){
    	        Gate::define($permission, function(User $user)use($permission){
    	            $cacheKey = 'user.' . $user->id . '.permissions';
    	            $userPermissions = Cache::get($cacheKey);
    	            
    	            if (! $userPermissions) {
    	                $userClosure = function ($query) use ($user) {
    	                    $query->where('users.id', '=', $user->id);
    	                };
    	                
    	                $userPermissions = Permission::query()
    	                ->whereHas('roles', function ($query) use($userClosure) {
    	                    $query->where('active', '=', 1)->whereHas('users', $userClosure);
    	                })->groupBy(['ry_admin_permissions.id', 'ry_admin_permissions.name'])->where('active', '=', 1)->pluck('name');
    	                Cache::put($cacheKey, $userPermissions->toArray());
    	            } else {
    	                $userPermissions = collect($userPermissions);
    	            }
    	            
    	            if ($userPermissions) {
    	                $altPermissions = Permission::alts($permission);
    	                return null !== $userPermissions->first(function (string $ident) use($altPermissions) {
    	                    return \in_array($ident, $altPermissions, true);
    	                });
    	            }
    	            
    	            return false;
    	        });
    	    });
    	}
    	
    	Blade::directive("d", function($expression){
    	    $ar = explode(":", $expression, 2);
    	    if(count($ar)==1) {
    	        $subsection = "text";
    	        $value = $expression;
    	    }
    	    else {
    	        $subsection = $ar[0];
    	        $value = $ar[1];
    	    }
    	    return <<<HERE
    	        <?php 
                if(env('APP_DEBUG') && isset(\$_GET["debug"])) {
                    \$d_sections = \Illuminate\Support\Facades\Cache::get("ryadmin.sections");
            	       if(!\$d_sections)
            	           \$d_sections = [];
            	       if(!isset(\$d_sections["$subsection"]))
            	           \$d_sections["$subsection"] = [];
            	       \$d_sections["$subsection"][] = "$value";
            	       \Illuminate\Support\Facades\Cache::put("ryadmin.sections", \$d_sections, 480);
                    echo ' data-section="$expression" ';
                }
                ?>
HERE;
    	});
    	
	    Event::listen("composing:*", function($name, $views){
	        if(isset($_GET["json"])) {
	            foreach($views as $view) {
	                app("ryadmin")->setData($view->getData());
	                app("ryadmin")->terminate();
	                return;
	            }
	        }
	        if(isset($_GET["debug"])) {
	            $ar = [];
	            foreach($views as $view) {
	                $ar[] = $view->getData();
	            }
	            app("ryadmin")->push($ar);
	        }
	    });
	    
        Event::listen("*", function(){
            $args = func_get_args();
            $d = $args;
            //Storage::disk('local')->append("events.log", $d[0]."\n");
        });
        
        View::composer('ryadmin::*', AuthComposer::class);
        View::composer('rymanager::*', AuthComposer::class);
        View::composer('manager::*', AuthComposer::class);
        
        Event::listen(RequestHandled::class, function(){
            if(isset($_GET["debug"])) {
                app("ryadmin")->terminate();
            }
        });
        
        Event::listen("ryadminnotify*", function($eventName, array $data){
            $site = app("centrale")->getSite();
            
            if(!Storage::disk("local")->exists("events.log")) {
                $events = [];
            }
            else {
                $events = json_decode(Storage::disk("local")->get("events.log"), true);
            }
            $events[$eventName] = [
                "latest_execution" => Carbon::now()
            ];
            Storage::disk("local")->put("events.log", json_encode($events));
            if($site->nsetup['emailing'])
                list($to) = $data;
            else
                $to = env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com');
            
            if($eventName=='ryadminnotify_insert_user') {
                Mail::to($to)->send(new UserInsertCaught($data));
            }
            else {
                $templates = NotificationTemplate::where("events", "LIKE", '%'.$eventName.'%')
                ->where("channels", "LIKE", '%MailSender%')->get();
                foreach($templates as $template) {
                    Mail::to($to)->send(new EventCaught($template, $data));
                }
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton("ryadmin", function(){
            return new RyAdmin();
        });
    	$this->app->singleton("admin", function(){
    		return new Administration();
    	});
    	$this->app->singleton("rygame.admin", function($app){
    		return new Admin();
    	});
    	$this->commands("rygame.admin");
    	$this->app->singleton("rygame.user0", function($app){
    		return new UserZero();
    	});
    	$this->commands("rygame.user0");
    	$this->app->singleton("ryadmin.section", function($app){
    	    return new RegisterLayoutSection();
    	});
    	$this->commands("ryadmin.section");
    	$this->app->singleton("ryadmin.allow", function($app){
    	    return new Ability();
    	});
    	$this->commands("ryadmin.allow");
    	$this->app->singleton(RegisterEvent::class, function($app){
    	    return new RegisterEvent();
    	});
    	$this->commands(RegisterEvent::class);
    }
    public function map()
    {    	
    	if (! $this->app->routesAreCached()) {
    		//$this->app['router']->middleware('admin', '\Ry\Admin\Http\Middleware\Administration');
    		$this->app['router']->group(['namespace' => 'Ry\Admin\Http\Controllers', 'middleware' => 'web'], function(){
    			require __DIR__.'/../Http/routes.php';
    		});
    	}
    }
}