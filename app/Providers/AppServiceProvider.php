<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        // Fix MySQL index length error (1071)
        Schema::defaultStringLength(191);

        // Forcer l'utilisation de resources/lang (AdminLTE)
        $this->app->useLangPath(resource_path('lang'));
    }
}
