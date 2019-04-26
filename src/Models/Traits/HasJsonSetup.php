<?php 
namespace Ry\Admin\Models\Traits;

trait HasJsonSetup
{
    public function getNsetupAttribute() {
        if($this->setup) {
            return json_decode($this->setup, true);
        }
        return [];
    }
    
    public function setNsetupAttribute($ar) {
        array_walk_recursive($ar, function(&$v, $k){
            if($k=='false' || $k=='true') {
                unset($v);
            }
            else {
                $v = is_numeric($v)?doubleval($v):$v;
                if($v==='false')
                    $v = false;
                if($v==='true')
                    $v = true;
            }
        });
        $this->setup = json_encode($ar);
    }
}
?>