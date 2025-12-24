<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bdg_obj1', function (Blueprint $table) {

            $table->bigIncrements('IDObj1');
            $table->string('designation', 100)->nullable();
            $table->string('Num', 50)->nullable();

            $table->timestamp('Creer_le')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('IDSection')->nullable();
            $table->string('designation_ara', 100)->nullable();
            $table->string('Reference', 20)->nullable();
            $table->longText('Observations')->nullable();

            $table->tinyInteger('dep_recette')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);

            $table->smallInteger('EXERCICE')->default(0);

            // Index
            $table->index('IDLogin', 'WDIDX_bdg_Obj1_IDLogin');
            $table->index('IDSection', 'WDIDX_bdg_Obj1_IDSection');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_obj1');
    }
};
