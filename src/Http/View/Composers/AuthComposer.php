<?php 
namespace Ry\Admin\Http\View\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use Auth;
use Ry\Admin\Models\Seo\CustomLayout;
use Twig\Loader\ArrayLoader;
use Twig\Environment;

class AuthComposer
{
    protected $me, $keys = [];
    
    public function __construct() {
        $this->me = app("ryadmin")->fullUser();
    }
    
    public function compose(View $view) {
        if($this->me) {
            $this->me->loadMissing('unseenNotifications');
            $this->me->loadMissing('contacts');
            $view->with('user', $this->me);
        }
        if(session()->has('message')) {
            $view->with("message", session('message'));
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
            $view->with('menugroup', [
                [
                    "menu" => "menu",
                    "title" => "Menu"
                ]
            ]);
            $view->with('usercontextualmenu', [
                [
                    "title" => "Mon profil",
                    "icon" => "icon-user",
                    "href" => "#"
                ],
                /*[
                 "title" => "Messages",
                 "icon" => "icon-envelope-open",
                 "href" => "#"
                 ],*/
                [
                    "title" => "Paramètres",
                    "icon" => "icon-settings",
                    "href" => "#"
                ],
                [
                    "title" => "separator"
                ],
                [
                    "title" => "Déconnexion",
                    "icon" => "icon-power",
                    "href" => "/logout"
                ]
            ]);
        }
      	$breadcrumbs = [
            "home" => [
                'title' => __('Accueil'),
                'href' => '/',
                'icon' => 'fa fa-home'
            ]
        ];
        $existingData = $view->getData();
        if(isset($existingData['parents'])) {
            $breadcrumbs = array_merge($breadcrumbs, $existingData['parents']);
        }
        if(isset($existingData['page']['href']))
            $breadcrumbs[$existingData['page']['href']] = $existingData['page'];
        $view->with("breadcrumbs", [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values($breadcrumbs),
            'current' => ['page']
        ]);
    }
}
?>