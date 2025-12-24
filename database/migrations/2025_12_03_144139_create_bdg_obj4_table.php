<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_obj4', function (Blueprint $table) {

            $table->string('designation', 50)->nullable();
            $table->string('Num', 50)->nullable();

            $table->timestamp('Creer_le')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('IDSection')->nullable();

            $table->bigIncrements('IDObj4');
            $table->integer('IDObj3')->nullable();

            $table->string('designation_ara', 100)->nullable();
            $table->string('Reference', 50)->nullable();

            $table->tinyInteger('dep_recette')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);

            $table->smallInteger('EXERCICE')->default(0);

            $table->bigInteger('IDBdg_Compte')->default(0);

            // Indexes
            $table->index('IDLogin', 'WDIDX_bdg_Obj4_IDLogin');
            $table->index('IDObj3', 'WDIDX_bdg_Obj4_IDObj3');
            $table->index('IDSection', 'WDIDX_bdg_Obj4_IDSection');

            // Foreign key
            $table->foreign('IDObj3')
                ->references('IDObj3')
                ->on('bdg_obj3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_obj4');
    }
};
