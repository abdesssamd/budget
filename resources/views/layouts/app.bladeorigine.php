<!DOCTYPE html>
<html lang="fr">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-Stock Budget | @yield('title', 'Gestion')</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>

        /* --- AJOUTEZ CECI DANS app.blade.php --- */
@media print {
    .sidebar, .top-navbar, .btn, .card-header, .pagination, .modal {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    /* On force l'affichage du tableau en noir et blanc propre */
    table { width: 100% !important; border: 1px solid #000 !important; }
}

        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --bg-light: #f4f6f9;
            --text-dark: #343a40;
            --sidebar-width: 280px; /* Légèrement élargi pour les sous-menus */
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        /* Scrollbar fine pour la sidebar */
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: #888; border-radius: 5px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }

        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h4 { font-weight: 700; margin: 0; letter-spacing: 1px; }
        .sidebar-header small { color: #bdc3c7; font-size: 0.8rem; }

        .sidebar-menu { padding: 15px 10px; list-style: none; margin: 0; }
        .sidebar-menu li { margin-bottom: 5px; }

        /* Liens principaux */
        .sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .sidebar-menu .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateX(5px); }
        .sidebar-menu .nav-link.active { background: var(--accent-color); color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .sidebar-menu i.menu-icon { width: 25px; text-align: center; margin-right: 10px; }
        .sidebar-menu .fa-chevron-down { transition: transform 0.3s; font-size: 0.75rem; margin-left: auto; }
        
        /* Rotation de la flèche quand menu ouvert */
        .nav-link[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }

        /* Sous-menus */
        .sidebar-submenu { list-style: none; padding-left: 20px; background: rgba(0,0,0,0.1); border-radius: 8px; margin-top: 5px; }
        .sidebar-submenu .nav-link { padding: 8px 15px; font-size: 0.9rem; font-weight: 400; opacity: 0.8; }
        .sidebar-submenu .nav-link:hover { opacity: 1; transform: none; background: rgba(255,255,255,0.05); }
        .sidebar-submenu .nav-link.active { color: #3498db; background: transparent; font-weight: bold; opacity: 1; box-shadow: none; }
        .sidebar-submenu .nav-link.active::before { content: "•"; margin-right: 8px; font-size: 1.2rem; }

        /* --- HEADER & CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: white;
            height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .page-content { padding: 30px; flex: 1; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-wallet me-2"></i>GSTOCK</h4>
            <small>Gestion Budgétaire</small>
        </div>
        
        <ul class="sidebar-menu">
            <!-- 1. TABLEAU DE BORD -->
            <li>
                <a href="/" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home menu-icon"></i> Tableau de bord
                </a>
            </li>

            <!-- SEPARATEUR -->
            <li class="mt-4 text-uppercase small text-muted px-3 fw-bold" style="font-size: 0.75rem;">Opérations</li>

            <!-- 2. ENGAGEMENTS & DEPENSES -->
            <li>
                <a href="#menuOps" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('engagement.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="fas fa-file-invoice-dollar menu-icon"></i> <span>Dépenses</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse sidebar-submenu {{ request()->routeIs('engagement.*') ? 'show' : '' }}" id="menuOps">
                    <li><a href="{{ route('engagement.create') }}" class="nav-link">Nouvel Engagement</a></li>
                    <li><a href="#" class="nav-link">Mandats de paiement</a></li>
                    <li><a href="#" class="nav-link">Ordres de virement</a></li>
                </ul>
            </li>

            <!-- SEPARATEUR -->
            <li class="mt-4 text-uppercase small text-muted px-3 fw-bold" style="font-size: 0.75rem;">Paramétrage</li>

            <!-- 3. NOMENCLATURE BUDGETAIRE (OBJ1 à OBJ5) -->
            <li>
                <a href="#menuNomenclature" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('bdg.*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('bdg.*') ? 'true' : 'false' }}">
                    <i class="fas fa-sitemap menu-icon"></i> <span>Nomenclature</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse sidebar-submenu {{ request()->routeIs('bdg.*') ? 'show' : '' }}" id="menuNomenclature">
                    <li>
                        <a href="{{ route('bdg.obj1.crud') }}" class="nav-link {{ request()->routeIs('bdg.obj1.crud') ? 'active' : '' }}">
                            Chapitres (OBJ1)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bdg.obj2.crud') }}" class="nav-link {{ request()->routeIs('bdg.obj2.crud') ? 'active' : '' }}">
                            Articles (OBJ2)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bdg.obj3.crud') }}" class="nav-link {{ request()->routeIs('bdg.obj3.crud') ? 'active' : '' }}">
                            Sous-Articles (OBJ3)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bdg.obj4.crud') }}" class="nav-link {{ request()->routeIs('bdg.obj4.crud') ? 'active' : '' }}">
                            Rubriques (OBJ4)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bdg.obj5.crud') }}" class="nav-link {{ request()->routeIs('bdg.obj5.crud') ? 'active' : '' }}">
                            Sous-Rubriques (OBJ5)
                        </a>
                    </li>
                </ul>
            </li>

            <!-- 4. CONFIGURATION GENERALE -->
            <li>
                <a href="#menuConfig" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('param.*') || request()->routeIs('exercices.*') || request()->routeIs('budgets.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="fas fa-cogs menu-icon"></i> <span>Param. Généraux</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse sidebar-submenu {{ request()->routeIs('param.*') || request()->routeIs('exercices.*') || request()->routeIs('budgets.*') ? 'show' : '' }}" id="menuConfig">
                    <li><a href="{{ route('exercices.index') }}" class="nav-link {{ request()->routeIs('exercices.*') ? 'active' : '' }}">Exercices</a></li>
                    <li><a href="{{ route('budgets.index') }}" class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}">Budgets</a></li>
                    <li><a href="{{ route('param.sections.index') }}" class="nav-link {{ request()->routeIs('param.sections.*') ? 'active' : '' }}">Sections</a></li>
                    <li><a href="{{ route('param.comptes.index') }}" class="nav-link {{ request()->routeIs('param.comptes.*') ? 'active' : '' }}">Comptes Budgétaires</a></li>
                    <li><a href="{{ route('param.banques.index') }}" class="nav-link {{ request()->routeIs('param.banques.*') ? 'active' : '' }}">Banques</a></li>
                </ul>
            </li>

            <!-- 5. TIERS (Fournisseurs, Employeurs) -->
            <li>
                <a href="#menuTiers" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('tiers.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="fas fa-users menu-icon"></i> <span>Tiers & RH</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse sidebar-submenu {{ request()->routeIs('tiers.*') ? 'show' : '' }}" id="menuTiers">
                    <li><a href="{{ route('tiers.fournisseurs.index') }}" class="nav-link">Fournisseurs</a></li>
                    <li><a href="{{ route('tiers.employeurs.index') }}" class="nav-link">Employeurs</a></li>
                    <li><a href="{{ route('tiers.fonctions.index') }}" class="nav-link">Fonctions</a></li>
                </ul>
            </li>

            <!-- 6. GEOGRAPHIE (Wilaya, Commune) -->
            <li>
                <a href="#menuGeo" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('geo.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="fas fa-map-marked-alt menu-icon"></i> <span>Géographie</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse sidebar-submenu {{ request()->routeIs('geo.*') ? 'show' : '' }}" id="menuGeo">
                    <li><a href="{{ route('geo.wilayas.index') }}" class="nav-link">Wilayas</a></li>
                    <li><a href="{{ route('geo.communes.index') }}" class="nav-link">Communes</a></li>
                    <li><a href="{{ route('geo.zones.index') }}" class="nav-link">Zones</a></li>
                </ul>
            </li>

        </ul>
    </nav>

    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center">
                <i class="fas fa-bars text-muted me-3 fs-4" style="cursor: pointer;"></i>
                <h5 class="m-0 fw-bold text-secondary">
                     @yield('header')
                </h5>
            </div>
            
            <div class="d-flex align-items-center gap-4">
                @if(Auth::check())
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="text-end lh-1 d-none d-sm-block me-2">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <small class="text-muted">Admin</small>
                        </div>
                        <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser1">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
            </div>
        </header>

        <!-- Contenu de la page -->
        <div class="page-content">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>