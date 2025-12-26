<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Création de la table historique des augmentations
        // On vérifie d'abord si la table n'existe pas déjà pour éviter une erreur
        if (!Schema::hasTable('bdg_budget_augmentation')) {
            Schema::create('bdg_budget_augmentation', function (Blueprint $table) {
                $table->id('IDAugmentation');
                
                $table->bigInteger('IDBudjet')->unsigned();
                $table->bigInteger('IDSection')->unsigned();
                
                $table->string('Type_budget', 50); // supplementaire, rectificatif, virement, report
                $table->string('Numero_decision', 100);
                $table->date('Date_decision');
                $table->string('Source_financement', 255);
                $table->string('Designation', 255);
                
                $table->decimal('Montant_augmentation', 24, 6)->default(0.000000);
                
                $table->text('Observations')->nullable();
                
                $table->timestamp('Date_augmentation')->useCurrent();
                $table->bigInteger('IDLogin')->default(0);
                
                $table->smallInteger('EXERCICE')->default(0);
                
                $table->timestamps();
                
                // Index
                $table->index('IDBudjet');
                $table->index('IDSection');
                $table->index('EXERCICE');
                $table->index('Type_budget');
                $table->index('Date_augmentation');
                
                // Foreign keys
                $table->foreign('IDBudjet')
                      ->references('IDBudjet')
                      ->on('bdg_budget')
                      ->onDelete('cascade');
                      
                $table->foreign('IDSection')
                      ->references('IDSection')
                      ->on('bdg_section')
                      ->onDelete('cascade');
            });
        }

        // 2. Ajout des colonnes dans bdg_budget
        // Utilisation de try/catch pour contourner le bug "generation_expression" des vieux MySQL
        
        // Ajout Montant_Primitif
        try {
            Schema::table('bdg_budget', function (Blueprint $table) {
                $table->decimal('Montant_Primitif', 24, 6)->default(0.000000)->after('Montant_Total');
            });
        } catch (\Exception $e) {
            // La colonne existe probablement déjà, on ignore l'erreur
        }

        // Ajout Montant_Restant
        try {
            Schema::table('bdg_budget', function (Blueprint $table) {
                $table->decimal('Montant_Restant', 24, 6)->default(0.000000)->after('Montant_Primitif');
            });
        } catch (\Exception $e) {
            // La colonne existe probablement déjà, on ignore l'erreur
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bdg_budget_augmentation');
        
        // Suppression des colonnes (enveloppé dans try/catch pour éviter les crashs au rollback)
        try {
            Schema::table('bdg_budget', function (Blueprint $table) {
                $table->dropColumn(['Montant_Primitif', 'Montant_Restant']);
            });
        } catch (\Exception $e) {
            // On ignore si les colonnes n'existent pas
        }
    }
};