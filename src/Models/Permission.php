<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\User;

class Permission extends Model
{
    protected $table = "ry_admin_permissions";
    
    protected $fillable = [
        'name', 'active'
    ];
    protected $casts = [
        'active' => 'bool'
    ];
    
    public function roles() {
        return $this->belongsToMany(Role::class, 'ry_admin_role_permissions', 'permission_id', 'role_id');
    }
    
    public static function alts($permission) {
        $altPermissions = ['*', $permission];
        $permParts = explode('.', $permission);
        
        if ($permParts && count($permParts) > 1) {
            $currentPermission = '';
            for ($i = 0; $i < (count($permParts) - 1); $i++) {
                $currentPermission .= $permParts[$i] . '.';
                $altPermissions[] = $currentPermission . '*';
            }
        }
        
        return $altPermissions;
    }
    
    public static function authorize($controller) {
        $ability = str_replace(['httpcontrollers', '.at.'], '.', str_slug(Route::current()->action['controller'], '.'));
        $permissions = Cache::get('ryadmin.permissions');
        if(in_array($ability, $permissions, true))
            $controller->authorize($ability);
        return $ability;
    }
}
