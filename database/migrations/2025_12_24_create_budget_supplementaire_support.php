<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
 

        // Créer une table de liaison pour tracer l'historique des répartitions
        Schema::create('bdg_repartition_history', function (Blueprint $table) {
            $table->id('IDHistory');
            
            $table->bigInteger('IDOperation_Source')->unsigned(); // Budget supplémentaire source
            $table->bigInteger('IDOperation_Cible')->unsigned(); // Répartition cible
            
            $table->decimal('Montant_reparti', 24, 6)->default(0.000000);
            $table->string('Type_source', 50); // supplementaire, rectificatif, virement, report
            
            $table->timestamp('Date_repartition')->useCurrent();
            $table->bigInteger('IDLogin')->default(0);
            
            $table->text('Commentaire')->nullable();
            
            $table->timestamps();
            
            // Index pour performances
            $table->index('IDOperation_Source');
            $table->index('IDOperation_Cible');
            $table->index('Date_repartition');
            
            // Foreign keys
            $table->foreign('IDOperation_Source')
                  ->references('IDOperation_Budg')
                  ->on('bdg_operation_budg')
                  ->onDelete('cascade');
                  
            $table->foreign('IDOperation_Cible')
                  ->references('IDOperation_Budg')
                  ->on('bdg_operation_budg')
                  ->onDelete('cascade');
        });

        // Table pour les types de budgets supplémentaires
        Schema::create('bdg_type_budget_supplementaire', function (Blueprint $table) {
            $table->id('IDType');
            $table->string('Code', 20)->unique(); // BS, BR, VC, RC
            $table->string('Designation', 100);
            $table->string('Designation_ara', 100)->nullable();
            $table->text('Description')->nullable();
            $table->boolean('Actif')->default(true);
            $table->integer('Ordre')->default(0);
            $table->timestamps();
        });

        // Insérer les types par défaut
        DB::table('bdg_type_budget_supplementaire')->insert([
            [
                'Code' => 'BS',
                'Designation' => 'Budget Supplémentaire',
                'Designation_ara' => 'ميزانية إضافية',
                'Description' => 'Crédits additionnels en cours d\'exercice',
                'Ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Code' => 'BR',
                'Designation' => 'Budget Rectificatif',
                'Designation_ara' => 'ميزانية تصحيحية',
                'Description' => 'Modification du budget initial',
                'Ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Code' => 'VC',
                'Designation' => 'Virement de Crédits',
                'Designation_ara' => 'تحويل اعتمادات',
                'Description' => 'Transfert entre lignes budgétaires',
                'Ordre' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Code' => 'RC',
                'Designation' => 'Report de Crédits',
                'Designation_ara' => 'ترحيل اعتمادات',
                'Description' => 'Crédits reportés de l\'exercice précédent',
                'Ordre' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bdg_repartition_history');
        Schema::dropIfExists('bdg_type_budget_supplementaire');
     
    }
};
