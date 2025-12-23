<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // On dit à Laravel d'utiliser le dossier resources/lang
        // Cela règle le conflit avec AdminLTE
        $this->app->useLangPath(resource_path('lang'));
    }
}