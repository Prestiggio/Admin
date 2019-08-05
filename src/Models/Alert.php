<?php

namespace Ry\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Ry\Admin\Models\Model as EventModel;

class Alert extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_admin_alerts";
    
    protected $appends = ['nsetup'];
}
