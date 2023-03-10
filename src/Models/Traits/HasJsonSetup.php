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
    
    public static function unescape(&$ar) {
        array_walk_recursive($ar, function(&$v, $k){
            if($v==='false')
                $v = false;
            if($v==='true')
                $v = true;
        });
        return $ar;
    }
    
    public function getHidden()
    {
        if(!in_array("setup", $this->hidden))
            return array_merge($this->hidden, ['setup']);
        return $this->hidden;
    }

    public function updateSetup($key, $value=null) {
        $ar = [];
        if(is_string($key)) {
            $ar[$key] = $value;
        }
        elseif(is_array($key)) {
            $ar = $key;
        }
        $setup = $this->nsetup;
        foreach($ar as $k=>$v) {
            if(is_null($v)) {
                unset($setup[$k]);
            }
            else {
                $setup[$k] = $v;
            }
        }
        $this->nsetup = $setup;
        $this->save();
    }
}
?>