<?php 
namespace Ry\Admin\Interfaces;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

interface ThemeOverride
{
    /**
     * @param Controller $instance
     * @param string $method
     * @param Request $request
     * @return $instance->$method($request);
     */
    public function override($instance, $method, Request $request);
    
    public function scripts();
    
    public function styles();
}
?>