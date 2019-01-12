<?php 
namespace Ry\Admin\Models\Traits;

use Ry\Admin\Models\UserPreference;

trait HasPreferencesTraits
{
    public function preference() {
        return $this->hasOne(UserPreference::class, "user_id");
    }
    
    public function preferences() {
        return $this->hasMany(UserPreference::class, "user_id");
    }
}
?>