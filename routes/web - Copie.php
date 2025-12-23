<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\BudgetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- ZONE DE TEST (Accessible publiquement pour debug) ---
Route::get('/test', function () {
    return "ROUTE OK";
});

// --- GROUPE PROTÉGÉ (Tout ce qui est ici nécessite une connexion) ---
Route::middleware('auth')->group(function () {

    // Paramètres
    Route::get('/parametres', [SettingController::class, 'index'])->name('parametres.index');
    Route::post('/parametres', [SettingController::class, 'update'])->name('parametres.update');

    // Gestion Exercices (Déplacé À L'INTÉRIEUR du groupe auth)
    Route::get('/exercices', [ExerciceController::class, 'index'])->name('exercices.index');
    Route::post('/exercices', [ExerciceController::class, 'store'])->name('exercices.store');

    // Gestion Budgets (Déplacé À L'INTÉRIEUR du groupe auth)
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/selectionner/{id}', [BudgetController::class, 'selectionner'])->name('budgets.selectionner');



    Route::get('/test', function () {
    return "ROUTE MISE A JOUR !!!";
});
    Route::get('/produits', function () {
        return view('produits');
    })->name('produits.index');

}); // <--- On ferme le groupe AUTH ici à la fin

require __DIR__.'/auth.php';