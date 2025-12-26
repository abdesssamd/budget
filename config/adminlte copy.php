<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */
    'title' => 'G-Stock',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */
    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */
    'google_fonts' => [
        'allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */
    'logo' => '<b>G-Stock</b>Budget',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'G-Stock Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */
    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader
    |--------------------------------------------------------------------------
    */
    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */
    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */
    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */
    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,
    'sidebar_nav_flat' => true, 

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */
    'right_sidebar' => true,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => false,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */
    'use_route_url' => false,
    'dashboard_url' => '/',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    */
    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */
    'menu' => [
        // --- NAVBAR ---
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => false,
        ],
        [
            'text' => '',
            'icon' => 'fas fa-moon',
            'icon_color' => 'gray',
            'url'  => '#',
            'topnav_right' => true,
            'id' => 'dark-mode-toggle',
        ],
        [
            'text'    => 'Langue',
            'icon'    => 'fas fa-globe',
            'topnav_right' => true,
            'submenu' => [
                [
                    'text' => 'Français',
                    'url'  => 'lang/fr',
                    'icon' => 'fas fa-flag',
                ],
                [
                    'text' => 'العربية',
                    'url'  => 'lang/ar',
                    'icon' => 'fas fa-flag',
                ],
            ],
        ],
        [
            'type'         => 'navbar-notification',
            'id'           => 'my-notification',
            'icon'         => 'fas fa-bell',
            'url'          => '#',
            'topnav_right' => true,
            'dropdown_mode'   => true,
            'dropdown_flabel' => 'Toutes les notifications',
        ],

        // --- SIDEBAR ---
        [
            'text' => 'menu.dashboard',
            'url'  => '/',
            'icon' => 'fas fa-fw fa-home',
        ],
        
        ['header' => 'menu.operations'],
        [
            'text'    => 'menu.expenses',
            'icon'    => 'fas fa-fw fa-file-invoice-dollar',
            'submenu' => [
                // 1. Mise en place du budget
                [
                    'text' => 'Incorporation Globale',
                    'route'  => 'ops.global',
                    'icon' => 'fas fa-fw fa-sack-dollar',
                ],
                [
                    'text' => 'Répartition',
                    'route'  => 'ops.repartition',
                    'icon' => 'fas fa-fw fa-chart-pie',
                ],
                
                // 2. Exécution de la dépense
                [
                    'text' => 'menu.purchase_orders', // Bon de Commande (1)
                    'route'  => 'operations.bc',
                    'icon' => 'fas fa-fw fa-shopping-cart',
                ],
                [
                    'text' => 'menu.new_engagement', // Engagement (2)
                    'route'  => 'engagement.create',
                    'icon' => 'fas fa-fw fa-file-signature',
                ],
                [
                    'text' => 'menu.liquidation', // Liquidation (3)
                    'route'  => 'operations.liquidation',
                    'icon' => 'fas fa-fw fa-file-invoice',
                ],
                [
                    'text' => 'menu.mandate', // Mandatement (4)
                    'route'  => 'operations.mandat',
                    'icon' => 'fas fa-fw fa-money-check-alt',
                ],
            ],
        ],
        
        [
            'text'    => 'menu.revenue',
            'icon'    => 'fas fa-fw fa-hand-holding-usd',
            'route'   => 'operations.recette',
        ],

        ['header' => 'menu.configuration'],
        
        // 1. Nomenclature
        [
            'text'    => 'menu.nomenclature',
            'icon'    => 'fas fa-fw fa-sitemap',
            'submenu' => [
                 ['text' => 'menu.sections', 'route' => 'param.sections.index', 'icon' => 'fas fa-fw fa-building'],
                ['text' => 'menu.chapters', 'route' => 'bdg.obj1.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.articles', 'route' => 'bdg.obj2.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.sub_articles', 'route' => 'bdg.obj3.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.rubrics', 'route' => 'bdg.obj4.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.sub_rubrics', 'route' => 'bdg.obj5.crud', 'icon' => 'far fa-fw fa-circle'],
            ],
        ],

        // 2. Paramètres Généraux
        [
            'text'    => 'menu.general_params',
            'icon'    => 'fas fa-fw fa-cogs',
            'submenu' => [
                [
                    'text' => 'Configuration Globale', 
                    'route'  => 'parametres.index',
                    'icon' => 'fas fa-fw fa-sliders-h',
                    'active' => ['parametres*'],
                ],
                ['text' => 'menu.exercises', 'route' => 'exercices.index', 'icon' => 'fas fa-fw fa-calendar-alt'],
                ['text' => 'menu.budgets', 'route' => 'budgets.index', 'icon' => 'fas fa-fw fa-coins'],
               
                ['text' => 'menu.accounts', 'route' => 'param.comptes.index', 'icon' => 'fas fa-fw fa-book'],
                ['text' => 'menu.banks', 'route' => 'param.banques.index', 'icon' => 'fas fa-fw fa-university'],
            ],
        ],

        // 3. Tiers & RH
        [
            'text'    => 'menu.tiers_rh',
            'icon'    => 'fas fa-fw fa-users',
            'submenu' => [
                ['text' => 'menu.suppliers', 'route' => 'tiers.fournisseurs.index', 'icon' => 'fas fa-fw fa-truck'],
                ['text' => 'menu.employers', 'route' => 'tiers.employeurs.index', 'icon' => 'fas fa-fw fa-building'],
                ['text' => 'menu.functions', 'route' => 'tiers.fonctions.index', 'icon' => 'fas fa-fw fa-briefcase'],
            ],
        ],

        // 4. Géographie
        [
            'text'    => 'menu.geography',
            'icon'    => 'fas fa-fw fa-map-marked-alt',
            'submenu' => [
                ['text' => 'menu.wilayas', 'route' => 'geo.wilayas.index', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.communes', 'route' => 'geo.communes.index', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.zones', 'route' => 'geo.zones.index', 'icon' => 'far fa-fw fa-circle'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */
    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */
    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'vendor/datatables/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'vendor/datatables/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'vendor/datatables/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'vendor/select2/js/select2.full.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'vendor/select2/css/select2.min.css',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'vendor/sweetalert2/sweetalert2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'vendor/sweetalert2/sweetalert2.min.css',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */
    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */
    'livewire' => true,
];