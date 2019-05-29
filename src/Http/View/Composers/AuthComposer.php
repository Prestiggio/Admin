<?php 
namespace Ry\Admin\Http\View\Composers;

use App\User;
use Illuminate\View\View;
use Auth;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\LayoutSection;

class AuthComposer
{
    protected $me, $keys = [];
    
    public function __construct() {
        $this->me = app("ryadmin")->fullUser();
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
                $view->with('admin', []);
            }
            $sections = app("ryadmin")->getSections($guard);
            foreach($sections as $section) {
                $sitemap = array_merge($sitemap, $section->setup);
                $view->with($section->name, $section->setup);
            }
        }
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
}
?>