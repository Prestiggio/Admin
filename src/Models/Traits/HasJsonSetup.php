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
        $this->setup = json_encode($ar);
    }
}
?>