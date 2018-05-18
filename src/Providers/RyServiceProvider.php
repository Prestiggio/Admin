<?php

namespace Ry\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Ry\Admin\Console\Commands\Admin;
use Ry\Admin\Console\Commands\UserZero;
use Ry\Admin\Http\Middleware\Administration;

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
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
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