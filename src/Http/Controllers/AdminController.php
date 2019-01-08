<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ry\Admin\Models\LanguageTranslation;
use Illuminate\Support\Facades\Cache;
use Ry\Admin\Http\Traits\LanguageTranslationController;

class AdminController extends Controller
{
    use LanguageTranslationController;
    
    protected $theme = "ryadmin";
    
    public function __construct() {
        $this->middleware('adminauth:admin')->except(['login']);
    }
    
    public function index($action=null, Request $request) {
        if(!$action)
            return $this->get_dashboard($request);
        $action = str_replace('-', '_', $action);
        $action = $request->getMethod() . '_' . $action;
        if($action!='' && method_exists($this, $action))
            return $this->$action($request);
        return ["ty zao io action io euuuh" => $action, 'za' => auth('admin')->user(), 'goto' => url('/logout')];
    }
    
    public function test() {
        return LanguageTranslation::writeToFiles();
    }
    
    public function post_menus(Request $request) {
        $ar = $request->all();
        $sections = Cache::get("ryadmin.sections");
        return view("rycentrale::admin.dialogs.menus", [
            "navigationByRole" => [
                "page" => $ar,
                "roles" => [
                    [
                        "name" => "Administrateur",
                        "menus" => [
                            [
                                "name" => $sections["menu"][0],
                                "tree" => [
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
                                ]
                            ]
                        ]
                    ],
                    [
                        "name" => "Manager",
                        "menus" => [
                            [
                                "name" => $sections["menu"][1],
                                "tree" => [
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
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "page" => $ar]);
    }
    
    public function get_logout() {
        auth('admin')->logout();
        return redirect('/');
    }
}