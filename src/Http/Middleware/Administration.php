<?php namespace Ry\Admin\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Administration extends Middleware
{

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if(!preg_match('/^admin\./i', $request->getHost()))
                return '/admin/login';
        }
        
        $user = auth('admin')->user();
        
        if(!$user)
            return '/login';
            
        if(!$user->isAdmin()) {
            return "/";
        }
    }

}
