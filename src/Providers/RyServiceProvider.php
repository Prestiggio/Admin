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
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Ry\Admin\Console\Commands\AdminModel;
use Ry\Admin\Models\Alert;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Ry\Admin\Http\Middleware\LangMiddleware;
use Ry\Admin\Models\Timeline;
use \ReflectionClass;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Console\Commands\Gettext;

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
	    */
    	$this->publishes([
    			__DIR__.'/../assets/public' => public_path('vendor/ryadmin'),
    	], "public");
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
            /*$event = Alert::where('code', '=', $d[0])->first();
            if($event) {
                $setup = $event->nsetup;
                $setup['last_execution'] = Carbon::now();
                $event->nsetup = $setup;
            }*/
        });
        
        View::composer('*', AuthComposer::class);
        
        Event::listen(RequestHandled::class, function(){
            if(isset($_GET["debug"])) {
                app("ryadmin")->terminate();
            }
        });
        
        Event::listen("ryadminnotify*", function($eventName, array $data){
            $site = app("centrale")->getSite();
            if($site->nsetup['emailing'])
                list($to) = $data;
            else
                $to = isset($site->nsetup['contact']['email']) ? $site->nsetup['contact']['email'] : env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com');
            $templates = NotificationTemplate::whereHas("alerts", function($q)use($eventName){
                $q->whereCode($eventName);
            })
            ->where("channels", "LIKE", '%MailSender%')->get();
            if($templates->count()>0) {
                foreach($templates as $template) {
                    Mail::to($to)->send(new EventCaught($template, $data));
                }
            }
            elseif($eventName=='ryadminnotify_insert_user') {
                Mail::to($to)->send(new UserInsertCaught($data));
            }
        });
        
        $middlewareGroups = $this->app->router->getMiddlewareGroups();
        $middlewareGroups['web'][] = LangMiddleware::class;
        $this->app->router->middlewareGroup('web', $middlewareGroups['web']);
        
        $this->app->booted(function(){
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function(){
                $timelines = Timeline::where('save_at', '<=', Carbon::now()->toDateTimeString())
                ->whereNull('action')->get();
                if($timelines->count()>0)
                    Model::unguard();
                foreach($timelines as $timeline) {
                    if($timeline->serializable_id>0) {
                        Timeline::where('serializable_type', '=', $timeline->serializable_type)
                        ->where('serializable_id', '=', $timeline->serializable_id)
                        ->where('active', '=', true)
                        ->update([
                            'active' => false
                        ]);
                        $row = $timeline->serializable;
                    }
                    else {
                        Timeline::where('serializable_type', '=', $timeline->serializable_type)
                        ->whereNull('serializable_id')
                        ->where('active', '=', true)
                        ->update([
                            'active' => false
                        ]);
                        $serialized = new ReflectionClass($timeline->serializable_type);
                        $row = $serialized->newInstance();
                    }
                    $setup = $timeline->nsetup;
                    if(isset($setup['nsetup'])) {
                        $row->nsetup = $setup['nsetup'];
                        unset($setup['nsetup']);
                    }
                    foreach($setup as $k=>$v) {
                        if(is_array($v)) {
                            unset($setup[$k]);
                        }
                    }
                    unset($setup['id']);
                    //Log::info('minuteur ' . print_r($setup, true));
                    if($timeline->serializable_id>0) {
                        unset($setup['created_at']);
                        unset($setup['updated_at']);
                        $row->update($setup);
                        $row->save();
                        $action = 'updated';
                    }
                    else {
                        $row->fill($setup);
                        $row->save();
                        $timeline->serializable_id = $row->id;
                        $action = 'created';
                    }
                    $timeline->active = true;
                    $timeline->action = $action;
                    $timeline->save();
                }
                if($timelines->count()>0)
                    Model::reguard();
                
                $timelines = Timeline::where('delete_at', '<=', Carbon::now()->toDateTimeString())
                    ->whereNull('action')->get();
                foreach($timelines as $timeline) {
                    if($timeline->serializable_id>0) {
                        $reversion = $timeline->reversion;
                        while($reversion && $reversion->delete_at<=Carbon::now()) {
                            $reversion = $reversion->reversion;
                        }
                        if($reversion && $reversion->delete_at>Carbon::now()) {
                            $row = $timeline->serializable;
                            $setup = $reversion->nsetup;
                            if($reversion->serializable_id>0) {
                                unset($setup['created_at']);
                                unset($setup['updated_at']);
                                $row->update($setup);
                                $row->save();
                                $action = 'updated';
                            }
                            else {
                                $row->fill($setup);
                                $row->save();
                                $reversion->serializable_id = $row->id;
                                $action = 'created';
                            }
                            $reversion->active = true;
                            $reversion->action = $action;
                            $reversion->save();
                        }
                        else {
                            $timeline->serializable->delete();
                            $timeline->active = false;
                            $timeline->action = 'deleted';
                            $timeline->save();
                        }
                    }
                    else {
                        //just delete if it's not linked to any record
                        $timeline->delete();
                    }
                }
                
            })->everyMinute();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /*$this->app->singleton("rylang", function(){
            return new LangMiddleware();
        });*/
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
    	
    	$this->app->singleton('ryadmin.models', function($app){
    	    return new AdminModel();
    	});
    	$this->commands('ryadmin.models');
    	
    	$this->app->singleton('ryadmin.gettext', function($app){
            return new Gettext(); 
    	});
    	$this->commands('ryadmin.gettext');
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