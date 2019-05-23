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
        static::unescape($ar);
        $this->setup = json_encode($ar);
    }
    
    public static function unescape($ar) {
        array_walk_recursive($ar, function(&$v, $k){
            if(!preg_match("/^0\d+/", $v))
                $v = is_numeric($v)?doubleval($v):$v;
            if($v==='false')
                $v = false;
            if($v==='true')
                $v = true;
        });
        return $ar;
    }
}
?>