<?php namespace Ry\Admin\Http\Middleware;

use Closure;

class Administration {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$user = auth()->user();
		
		if(!$user)
			return redirect()->guest('login');
		
		if(!$user->isAdmin()) {
			return redirect("/");
		}
		
		return $next($request);
	}

}
