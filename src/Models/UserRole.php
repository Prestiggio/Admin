<?php 
namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = "ry_admin_user_roles";
    
    protected $fillable = ["role_id"];
}
?>