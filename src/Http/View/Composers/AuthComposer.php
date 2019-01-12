<?php 
namespace Ry\Admin\Http\View\Composers;

use Illuminate\View\View;
use Auth;
use Ry\Admin\Models\Layout\Layout;
use Ry\Admin\Models\Layout\LayoutSection;

class AuthComposer
{
    protected $me;
    
    public function __construct() {
        $this->me = Auth::user();
    }
    
    public function compose(View $view) {
        $view->with('user', $this->me);
        $view->with('admin', [
            [
                'title' => __('Autorisations et raccourcis'),
                'icon' => 'fa fa-shield-alt',
                'href' => '#dialog/menus'
            ],
            [
                'title' => __('Traductions'),
                'icon' => 'fa fa-language',
                'href' => '/traductions'
            ]
        ]);
        if($this->me) {
            $guard = $this->me->guard;
            $sections = LayoutSection::whereHas("layout", function($q)use($guard){
                $q->where("name", "=", $guard);
            })->get();
            foreach($sections as $section) {
                $view->with($section->name, $section->setup);
            }
        }
        $view->with('menuka', [
            [
                "title" => "Tableau de bord",
                "href" => "/",
                "icon" => "fa fa-tachometer-alt"
            ],
            [
                "title" => "Gestion générale",
                "icon" => "fa fa-cogs",
                "children" => [
                    [
                        "title" => "Utilisateurs",
                        "children" => [
                            [
                                "title" => "Votre profil",
                                "href" => "/utilisateurs/mon-compte"
                            ],
                            [
                                "title" => "Ajouter utilisateur",
                                "href" => "/utilisateurs/ajout"
                            ]
                        ]
                    ],
                    [
                        "title" => "Banana",
                        "children" => [
                            [
                                "title" => "Votre profil",
                                "href" => "/utilisateurs/mon-compte"
                            ],
                            [
                                "title" => "Ajouter utilisateur",
                                "icon" => "fa fa-cogs",
                                "children" => [
                                    [
                                        "title" => "Votre profil",
                                        "href" => "/utilisateurs/mon-compte"
                                    ],
                                    [
                                        "title" => "Ajouter utilisateur",
                                        "children" => [
                                            [
                                                "title" => "Votre profil",
                                                "href" => "/utilisateurs/mon-compte"
                                            ],
                                            [
                                                "title" => "Ajouter utilisateur",
                                                "href" => "/utilisateurs/ajout"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "title" => "Utilisateurs",
                        "children" => [
                            [
                                "title" => "Votre profil",
                                "href" => "/utilisateurs/mon-compte"
                            ],
                            [
                                "title" => "Ajouter utilisateur",
                                "href" => "/utilisateurs/ajout"
                            ]
                        ]
                    ],
                    [
                        "title" => "Utilisateurs",
                        "children" => [
                            [
                                "title" => "Votre profil",
                                "href" => "/utilisateurs/mon-compte"
                            ],
                            [
                                "title" => "Ajouter utilisateur",
                                "href" => "/utilisateurs/ajout"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "title" => "Utilisateurs",
                "icon" => "fa fa-users",
                "children" => [
                    [
                        "title" => "Votre profil",
                        "href" => "/utilisateurs/mon-compte"
                    ],
                    [
                        "title" => "Ajouter utilisateur",
                        "href" => "/utilisateurs/ajout"
                    ]
                ]
            ],
            [
                "title" => "Utilisateurs",
                "icon" => "fa fa-users",
                "children" => [
                    [
                        "title" => "Votre profil",
                        "href" => "/utilisateurs/mon-compte"
                    ],
                    [
                        "title" => "Ajouter utilisateur",
                        "href" => "/utilisateurs/ajout"
                    ]
                ]
            ],
            [
                "title" => "Utilisateurs",
                "icon" => "fa fa-users",
                "children" => [
                    [
                        "title" => "Votre profil",
                        "href" => "/utilisateurs/mon-compte"
                    ],
                    [
                        "title" => "Ajouter utilisateur",
                        "href" => "/utilisateurs/ajout"
                    ]
                ]
            ],
        ]);
    }
}
?>