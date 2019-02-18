<?php 
namespace Ry\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ry\Admin\Models\Layout\LayoutSection;
use Auth;

class RyAdmin
{
    private $data = [];
    
    private $sections = [];
    
    private $cache, $me;
    
    public function __construct() {
        $this->cache = Cache::get('ryadmin.permissions');
    }
    
    public function fullUser() {
        if(!$this->me) {
            $this->me = User::with("profile")->find(Auth::user()->id)->append('thumb');
        }
        return $this->me;
    }
    
    public function getSections($guard) {
        if(!isset($this->sections[$guard])) {
            $this->sections[$guard] = LayoutSection::whereHas("layout", function($q)use($guard){
                $q->where("name", "=", $guard);
            })->get();
        }
        return $this->sections[$guard];
    }
    
    public function getCache() {
        return $this->cache;
    }
    
    public function setData($ar) {
        $this->data = $ar;
    }
    
    public function push($ar) {
        $this->data[] = $ar;
    }
    
    public function terminate() {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $response = response()->json($this->data);
        $response->send();
        $kernel->terminate(Request::capture(), $response);
        exit;
    }
}
?>