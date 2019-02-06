<?php 
namespace Ry\Admin\Http\View\Composers;

use Illuminate\View\View;
use Auth;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\LayoutSection;

class AuthComposer
{
    protected $me, $keys = [];
    
    public function __construct() {
        $this->me = Auth::user();
    }
    
    public function compose(View $view) {
        $view->with('user', $this->me);
        $data = $view->getData();
        if(isset($data['page'])) {
            
        }
        $sitemap = [];
        if($this->me) {
            $guard = $this->me->guard;
            if($guard=='admin') {
                $view->with('admin', [
                    [
                        'title' => __('autorisations_et_raccourcis'),
                        'icon' => 'fa fa-shield-alt',
                        'href' => '#dialog/menus',
                        'data' => 'page'
                    ],
                    [
                        'title' => ucfirst(__('traductions')),
                        'icon' => 'fa fa-language',
                        'href' => '/'.__('get_translations')
                    ]
                ]);
            }
            $sections = LayoutSection::whereHas("layout", function($q)use($guard){
                $q->where("name", "=", $guard);
            })->get();
            foreach($sections as $section) {
                $sitemap = array_merge($sitemap, $section->setup);
                $view->with($section->name, $section->setup);
            }
        }
        
        $this->test($sitemap);
        $view->with("tyar", $sitemap);
        $view->with("breadcrumbs", [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('accueil'),
                    'item' => '/'
                ]
            ],
            'current' => ['page']
        ]);
    }
    
    private function test(&$ar) {
        
    }
}
?>