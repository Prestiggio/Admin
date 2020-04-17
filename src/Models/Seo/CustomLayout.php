<?php

namespace Ry\Admin\Models\Seo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Collection;

class CustomLayout extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_admin_custom_layouts";
    
    private static $block_cached = false;
    
    private static $cached_blocks = [];
    
    public function blocks() {
        return $this->hasMany(CustomLayoutBlock::class, 'custom_layout_id');
    }
    
    public static function fetchBlocks() {
        if(!static::$block_cached) {
            static::$block_cached = true;
            $request = app(Request::class);
            $site = app("centrale")->getSite();
            $guard = 'admin';
            if($site && $site->nsetup['subdomains']) {
                foreach($site->nsetup['subdomains'] as $guard => $subdomain) {
                    if($subdomain==$request->getHost()) {
                        break;
                    }
                }
            }
            $wildcard_custom_layout = CustomLayout::whereRoute($guard)->first();
            $blocks = [];
            if($wildcard_custom_layout) {
                foreach($wildcard_custom_layout->blocks as $block) {
                    $blocks[$block->name] = $block;
                }
            }
            $query_custom_layout = static::whereRoute($request->route()->action['controller']);
            $action = $request->route('action', false);
            if($action)
                $query_custom_layout->where('ry_admin_custom_layouts.setup->parameters->action', $action);
            $page = $request->route('page', false);
            if($page)
                $query_custom_layout->where('ry_admin_custom_layouts.setup->parameters->page', $page);
            $custom_layout = $query_custom_layout->first();
            if($custom_layout) {
                foreach ($custom_layout->blocks as $block) {
                    $blocks[$block->name] = $block;
                }
            }
            static::$cached_blocks = new Collection(array_values($blocks));
        }
        return static::$cached_blocks;
    }
    
    public static function includes($vars=[]) {
        $locale = App::getLocale();
        $fallback_locale = config('app.fallback_locale');
        $includes = [];
        $blocks = static::fetchBlocks();
        foreach($blocks as $block) {
            foreach($block->medias as $media) {
                if($locale!=$fallback_locale && $block->lang==$fallback_locale) {
                    $includes[$block->name] = $media->path;
                }
            }
        }
        foreach($blocks as $block) {
            foreach($block->medias as $media) {
                if($block->lang==$locale) {
                    $includes[$block->name] = $media->path;
                }
            }
        }
        foreach($includes as $block_name => $include_path) {
            $loader = new \Twig_Loader_Array([
                'content' => Storage::disk('local')->get($include_path)
            ]);
            $twig = new \Twig_Environment($loader);
            ?>
            <div id="<?php echo $block_name; ?>">
            	<?php echo $twig->render("content", $vars); ?>
            </div>
            <?php
        }
    }
}
