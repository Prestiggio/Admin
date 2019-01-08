<?php 
namespace Ry\Admin\Http\View\Composers;

use Illuminate\View\View;
use Auth;

class AuthComposer
{
    protected $me;
    
    public function __construct() {
        $this->me = Auth::user();
    }
    
    public function compose(View $view) {
        $view->with('user', $this->me);
        $view->with('menu', [
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