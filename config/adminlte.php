<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'G-Stock',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
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
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
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
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
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
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
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
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    
    // Option pour faire survoler les menus (flat style)
    'sidebar_nav_flat' => true, 

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => true, // ACTIVÉ pour le panneau latéral caché
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true, // Slide-over effect
    'right_sidebar_push' => false, // Push content effect
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
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
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
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

        // --- SIDEBAR PRINCIPALE ---
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
                [
                    'text' => 'menu.new_engagement',
                    'route'  => 'engagement.create',
                    'icon' => 'fas fa-fw fa-plus',
                ],
                [
                    'text' => 'menu.liquidation',
                    'route'  => 'operations.liquidation',
                    'icon' => 'fas fa-fw fa-file-invoice',
                ],
                [
                    'text' => 'menu.mandate',
                    'route'  => 'operations.mandat',
                    'icon' => 'fas fa-fw fa-file-signature',
                ],
            ],
        ],
        
        // --- AJOUT DE L'ONGLET RECETTES ---
        [
            'text'    => 'menu.revenue', // Assurez-vous d'avoir ajouté cette clé dans fr.json/ar.json
            'icon'    => 'fas fa-fw fa-hand-holding-usd',
            'route'   => 'operations.recette', // Route vers le composant de gestion des recettes
        ],

        // --- GROUPE DES PARAMÈTRES (Sidebar) ---
        ['header' => 'menu.configuration'],
        
        // 1. التسمية (Nomenclature)
        [
            'text'    => 'menu.nomenclature',
            'icon'    => 'fas fa-fw fa-sitemap',
            'submenu' => [
                ['text' => 'menu.chapters', 'route' => 'bdg.obj1.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.articles', 'route' => 'bdg.obj2.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.sub_articles', 'route' => 'bdg.obj3.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.rubrics', 'route' => 'bdg.obj4.crud', 'icon' => 'far fa-fw fa-circle'],
                ['text' => 'menu.sub_rubrics', 'route' => 'bdg.obj5.crud', 'icon' => 'far fa-fw fa-circle'],
            ],
        ],

        // 2. المعاملات العامة (Paramètres Généraux)
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
                ['text' => 'menu.sections', 'route' => 'param.sections.index', 'icon' => 'fas fa-fw fa-building'],
                ['text' => 'menu.accounts', 'route' => 'param.comptes.index', 'icon' => 'fas fa-fw fa-book'],
                ['text' => 'menu.banks', 'route' => 'param.banques.index', 'icon' => 'fas fa-fw fa-university'],
            ],
        ],

        // 3. الأطراف والموارد البشرية (Tiers & RH)
        [
            'text'    => 'menu.tiers_rh',
            'icon'    => 'fas fa-fw fa-users',
            'submenu' => [
                ['text' => 'menu.suppliers', 'route' => 'tiers.fournisseurs.index', 'icon' => 'fas fa-fw fa-truck'],
                ['text' => 'menu.employers', 'route' => 'tiers.employeurs.index', 'icon' => 'fas fa-fw fa-building'],
                ['text' => 'menu.functions', 'route' => 'tiers.fonctions.index', 'icon' => 'fas fa-fw fa-briefcase'],
            ],
        ],

        // 4. الجغرافيا (Géographie)
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