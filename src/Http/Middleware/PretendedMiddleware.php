<?php namespace Ry\Admin\Http\Middleware;

use Closure;
use Ry\Admin\Models\Pretention;

class PretendedMiddleware
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
        if(!session()->has('pretention')) {
            return abort(403);
        }
        
        $pretention = Pretention::whereUsed(true)->find(session()->get('pretention')->id);
        if(!$pretention) {
            return abort(403);
        }
        
        return $next($request);
    }
}