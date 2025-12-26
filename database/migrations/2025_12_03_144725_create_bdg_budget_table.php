<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_budget', function (Blueprint $table) {

            $table->id('IDBudjet');

            $table->smallInteger('EXERCICE')->default(0);
            $table->string('designation', 50)->nullable();
            $table->tinyInteger('Archive')->default(0);

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();
		$table->decimal('Montant_Global', 24, 6)->default(0.000000);
        $table->decimal('Montant_Restant', 24, 6)->default(0.000000);
         $table->decimal('Montant_Primitif', 24, 6)->default(0.000000);
         $table->decimal('Montant_Total', 24, 6)->default(0.000000);
        
            $table->bigInteger('IDLogin')->default(0);
            $table->string('Reference', 20)->nullable();

            // Indexes
            $table->index('EXERCICE', 'WDIDX_bdg_Budget_EXERCICE');
            $table->index('IDLogin', 'WDIDX_bdg_Budget_IDLogin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_budget');
    }
};
