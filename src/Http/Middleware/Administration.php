<?php namespace Ry\Admin\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Administration extends Middleware
{

    protected function redirectTo($request)
    {
        $user = auth('admin')->user();
        
        if(!$user)
            return '/login';
            
        if(!$user->isAdmin()) {
            return "/";
        }
    }

}
