<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Ajouter cette ligne pour définir le chemin des ressources
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// AJOUTEZ CECI JUSTE AVANT LE RETURN DU FICHIER SI POSSIBLE, 
// MAIS COMME C'EST CHAINÉ, LE MIEUX EST DE LE FAIRE DANS AppServiceProvider
return $app;