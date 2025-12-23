<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_obj2', function (Blueprint $table) {

            $table->string('designation', 50)->nullable();
            $table->string('Num', 50)->nullable();

            $table->timestamp('Creer_le')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('IDSection')->nullable();

            $table->integer('IDObj2')->primary();
            $table->bigInteger('IDObj1')->nullable();

            $table->string('designation_ara', 100)->nullable();
            $table->string('Reference', 20)->nullable();
            $table->longText('Observations')->nullable();

            $table->tinyInteger('dep_recette')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);

            $table->smallInteger('EXERCICE')->default(0);

            $table->bigInteger('IDBdg_Compte')->default(0);

            // Indexes
            $table->index('IDLogin', 'WDIDX_bdg_Obj2_IDLogin');
            $table->index('IDObj1', 'WDIDX_bdg_Obj2_IDObj1');
            $table->index('IDSection', 'WDIDX_bdg_Obj2_IDSection');

            // Foreign key
            $table->foreign('IDObj1')
                ->references('IDObj1')
                ->on('bdg_obj1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_obj2');
    }
};
