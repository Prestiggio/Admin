<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class Archive extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_admin_archives";
}
