<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ImpressionController; // Import direct

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- ROUTE POUR CHANGER DE LANGUE ---
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');


// Page d'accueil
Volt::route('/', 'dashboard.home')->middleware(['auth', 'verified'])->name('dashboard');


// --- GROUPE AUTHENTIFIÉ ---
Route::middleware('auth')->group(function () {

    // Redirection nomenclature
    Route::get('/nomenclature', function() {
        return redirect()->route('bdg.obj1.crud');
    })->name('nomenclature.index');

    // ==========================================
    // 1. NOMENCLATURE BUDGETAIRE
    // ==========================================
    Volt::route('/nomenclature/obj1', 'budget.bdg-obj1-crud')->name('bdg.obj1.crud');
    Volt::route('/nomenclature/obj2', 'budget.bdg-obj2-crud')->name('bdg.obj2.crud');
    Volt::route('/nomenclature/obj3', 'budget.bdg-obj3-crud')->name('bdg.obj3.crud');
    Volt::route('/nomenclature/obj4', 'budget.bdg-obj4-crud')->name('bdg.obj4.crud');
    Volt::route('/nomenclature/obj5', 'budget.bdg-obj5-crud')->name('bdg.obj5.crud');

    // ==========================================
    // 2. PARAMETRES GENERAUX
    // ==========================================
    Volt::route('/budgets', 'budget.budgets-crud')->name('budgets.index');
    Volt::route('/param/sections', 'param.sections')->name('param.sections.index');
    Volt::route('/param/comptes', 'param.comptes')->name('param.comptes.index');
    Volt::route('/param/banques', 'param.banques')->name('param.banques.index');

    Route::get('/exercices', [ExerciceController::class, 'index'])->name('exercices.index');
    Route::post('/exercices', [ExerciceController::class, 'store'])->name('exercices.store');
    
    Route::get('/parametres', [SettingController::class, 'index'])->name('parametres.index');
    Route::post('/parametres', [SettingController::class, 'update'])->name('parametres.update');
    
    // ==========================================
    // 3. TIERS & RH
    // ==========================================
    Volt::route('/tiers/employeurs', 'tiers.employeurs')->name('tiers.employeurs.index');
    Volt::route('/tiers/fonctions', 'tiers.fonctions')->name('tiers.fonctions.index');
    Volt::route('/tiers/fournisseurs', 'tiers.fournisseurs')->name('tiers.fournisseurs.index');

    // ==========================================
    // 4. GEOGRAPHIE
    // ==========================================
    Route::get('/geo/wilayas', function(){ return "Wilayas (À faire)"; })->name('geo.wilayas.index');
    Route::get('/geo/communes', function(){ return "Communes (À faire)"; })->name('geo.communes.index');
    Route::get('/geo/zones', function(){ return "Zones (À faire)"; })->name('geo.zones.index');

    // ==========================================
    // 5. OPERATIONS BUDGETAIRES
    // ==========================================
    
    // 1. Incorporation
    Volt::route('/operations/global', 'operations.global-incorp')->name('ops.global');
    Volt::route('/operations/incorporation', 'operations.incorporation')->name('operations.incorporation');

    // 2. Répartition
    Volt::route('/operations/repartition', 'operations.repartition')->name('ops.repartition');

    // 3. Commandes
    Volt::route('/operations/bon-commande', 'operations.bon-commande')->name('operations.bc');

    // 4. Engagements
    Volt::route('/engagement/nouveau', 'operations.engagement')->name('engagement.create');

    // 5. Liquidation (Factures)
    Volt::route('/operations/liquidation', 'operations.liquidation')->name('operations.liquidation');

    // 6. Mandatement
    Volt::route('/operations/mandat', 'operations.mandat')->name('operations.mandat');
   // 7 recette 
   Volt::route('/operations/recette', 'operations.recette')->name('operations.recette');

    // ==========================================
    // 6. IMPRESSIONS GENERIQUES
    // ==========================================
    // Note : {id?} avec point d'interrogation pour rendre l'ID optionnel (ex: listes globales)
    Route::get('/imprimer/{dossier}/{fichier}/{id?}', [ImpressionController::class, 'print'])
        ->name('print.generique');

});

require __DIR__.'/auth.php';