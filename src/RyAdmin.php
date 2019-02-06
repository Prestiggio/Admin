<?php 
namespace Ry\Admin;

use Illuminate\Http\Request;

class RyAdmin
{
    private $data = [];
    
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