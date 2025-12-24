<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_compte', function (Blueprint $table) {

            $table->id('IDBdg_Compte');

            $table->string('Num_Compte', 20)->nullable();
            $table->string('designation', 50)->nullable();

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);

            $table->smallInteger('EXERCICE')->default(0);
            $table->tinyInteger('dep_recette')->nullable();

            // Indexes
            $table->index('EXERCICE', 'WDIDX_Bdg_Compte_EXERCICE');
            $table->index('IDLogin', 'WDIDX_Bdg_Compte_IDLogin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_compte');
    }
};
