<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_section', function (Blueprint $table) {
            $table->id('IDSection');
            $table->string('Num_section', 50)->nullable();
            $table->string('NOM_section', 100)->nullable();

            $table->timestamp('Creer_le')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->tinyInteger('Estmateriel')->default(0);
            $table->tinyInteger('EstMantGarde_fin_exrc')->default(0);
            $table->string('NOM_section_ara', 100)->nullable();
            $table->tinyInteger('dep_recette')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);

            $table->smallInteger('EXERCICE')->default(0);

            // Index
            $table->index('IDLogin', 'WDIDX_bdg_Section_IDLogin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_section');
    }
};
