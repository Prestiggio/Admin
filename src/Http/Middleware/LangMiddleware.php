<?php

namespace Ry\Admin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class LangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('lang')) {
            $lang = $request->get('lang');
            if(is_string($lang))
                session()->put('lang', $lang);
        }
        
        App::setLocale(session()->get('lang', App::getLocale()));
        
        View::share("lang", App::getLocale());
        
        return $next($request);
    }
}
