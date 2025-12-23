{{-- 1. FORÇAGE DE LA CONFIGURATION ADMINLTE AVANT LE CHARGEMENT --}}
@php
    // On détecte si la langue est l'arabe
    $isRtl = app()->getLocale() == 'ar';

    // On force la configuration RTL directement ici si la langue est 'ar'
    if($isRtl) {
        config(['adminlte.layout_rtl' => true]);
    }
@endphp

@extends('adminlte::page')

{{-- Titre de la page --}}
@section('title', 'G-Stock Budget')

{{-- En-tête de la page --}}
@section('content_header')
    @hasSection('header')
        <div class="d-flex justify-content-between">
            <h1>@yield('header')</h1>
        </div>
    @endif
@stop

{{-- Contenu Principal --}}
@section('content')
    <!-- DEBUG CODE SOURCE (DANS LE BODY) : La langue actuelle est "{{ app()->getLocale() }}" -->
    {{ $slot ?? '' }}
    @yield('content')
@stop

{{-- CSS Spécifique --}}
@section('css')
    {{-- 1. Police Cairo (Locale pour mode hors ligne) --}}
    <style>
        @font-face {
            font-family: 'Cairo';
            src: url('{{ asset('fonts/Cairo-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Cairo';
            src: url('{{ asset('fonts/Cairo-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
    </style>
    
    {{-- Styles Livewire --}}
    @livewireStyles

    {{-- 2. GESTION DU RTL (ARABE) --}}
    @if($isRtl)
        <!-- MODE RTL ACTIVÉ : Chargement des CSS spécifiques -->
        
        {{-- Bootstrap 4 RTL --}}
        <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.5.3/css/bootstrap.min.css">
        
        {{-- AdminLTE RTL --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/rtl/adminlte.rtl.min.css">
        
        <style>
            /* Surcharge pour forcer la police Cairo et l'alignement */
            body, h1, h2, h3, h4, h5, h6, .btn, .sidebar, .nav-link, .card-title, .brand-text {
                font-family: 'Cairo', sans-serif !important;
            }
            
            /* Correctifs spécifiques RTL */
            .content-header h1 { float: right; }
            .card-title { float: right; }
            .card-tools { float: left; }
            
            /* Inversion des marges pour les icônes */
            .mr-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
            .ml-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
            
            /* Sidebar : Forcer à droite */
            .main-sidebar { right: 0 !important; left: auto !important; }

            /* --- CORRECTIF CRITIQUE POUR LE CONTENU CACHÉ (ARABE) --- */
            /* On pousse le contenu vers la gauche de la largeur du menu (250px) */
            @media (min-width: 992px) {
                /* Quand le menu est OUVERT */
                body:not(.sidebar-collapse) .content-wrapper, 
                body:not(.sidebar-collapse) .main-footer, 
                body:not(.sidebar-collapse) .main-header {
                    margin-right: 250px !important;
                    margin-left: 0 !important;
                }

                /* Quand le menu est RÉDUIT (Collapsed) */
                body.sidebar-collapse .content-wrapper, 
                body.sidebar-collapse .main-footer, 
                body.sidebar-collapse .main-header {
                    margin-right: 4.6rem !important;
                    margin-left: 0 !important;
                }
            }
            
            /* Forcer les chiffres en LTR (Gauche à Droite) pour lisibilité (ex: montants) */
            /* On applique cela aux inputs numériques, aux badges et aux montants globaux */
            .ltr, 
            input[type="number"], 
            input[type="tel"], 
            .badge, 
            .info-box-number { 
                direction: ltr !important; 
                unicode-bidi: embed;
                text-align: right; 
            }
        </style>

        {{-- Script immédiat pour forcer l'attribut HTML --}}
        <script>
            document.documentElement.setAttribute('dir', 'rtl');
        </script>
    @else
        <!-- MODE LTR (Français/Défaut) -->
        <style>
            /* Police pour le mode Français aussi */
            body, h1, h2, h3, h4, h5, h6, .btn, .sidebar {
                font-family: 'Cairo', sans-serif !important;
            }
        </style>
    @endif

    {{-- 3. Styles pour l'impression --}}
    <style>
        @media print {
            .main-sidebar, .main-header, .content-header, .btn, .card-header, .pagination, .modal, .no-print {
                display: none !important;
            }
            .content-wrapper, .main-footer {
                margin: 0 !important;
                width: 100% !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            table { width: 100% !important; border: 1px solid #000 !important; }
            
            @if($isRtl)
                body { direction: rtl; text-align: right; }
                table th, table td { text-align: right; }
            @endif
        }
    </style>
@stop

{{-- JS Spécifique --}}
@section('js')
    @livewireScripts
    
    <script>
        $(document).ready(function() {
            // Gestion du Dark Mode
            var toggleSwitch = document.querySelector('#dark-mode-toggle');
            var body = document.querySelector('body');
            var icon = toggleSwitch ? toggleSwitch.querySelector('i') : null;

            // Appliquer le thème sauvegardé
            if(localStorage.getItem('theme') === 'dark') {
                body.classList.add('dark-mode');
                if(icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun', 'text-warning'); }
            }

            // Événement clic
            if(toggleSwitch) {
                toggleSwitch.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('dark-mode');
                    
                    if (body.classList.contains('dark-mode')) {
                        localStorage.setItem('theme', 'dark');
                        if(icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun', 'text-warning'); }
                    } else {
                        localStorage.setItem('theme', 'light');
                        if(icon) { icon.classList.remove('fa-sun', 'text-warning'); icon.classList.add('fa-moon'); icon.style.color = 'gray'; }
                    }
                });
            }
        });
    </script>
@stop