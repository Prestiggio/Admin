<?php

namespace Ry\Admin\Models\Seo;

use Illuminate\Database\Eloquent\Model;
use Ry\Medias\Models\Traits\MediableTrait;
use Ry\Admin\Models\Traits\HasJsonSetup;

class CustomLayoutBlock extends Model
{
    use MediableTrait, HasJsonSetup;
    
    protected $table = "ry_admin_custom_layout_blocks";
    
    public function layout() {
        return $this->belongsTo(CustomLayout::class, 'custom_layout_id');
    }
}
